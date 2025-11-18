<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\MerchantSupply;
use Illuminate\Support\Facades\DB;
use App\Notifications\OrderAccepted;
use App\Notifications\OrderRejected;
use App\Notifications\NewOrderReceived;

class OrderController extends Controller
{
    /**
     * Affiche les commandes selon le rôle (Couturier / Mercerie)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = null;

        if ($user->isCouturier()) {
            $query = $user->ordersAsCouturier()
                ->with(['items.merchantSupply', 'mercerie'])
                ->latest();
        } elseif ($user->isMercerie()) {
            $query = $user->ordersAsMercerie()
                ->with(['items.merchantSupply', 'couturier'])
                ->latest();
        }

        if ($query && $search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('mercerie', fn($m) => $m->where('name', 'like', "%$search%"))
                ->orWhere('id', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%");
            });
        }

        if ($query && ($startDate || $endDate)) {
            $query->whereBetween('created_at', [
                $startDate ? date('Y-m-d 00:00:00', strtotime($startDate)) : '2000-01-01 00:00:00',
                $endDate ? date('Y-m-d 23:59:59', strtotime($endDate)) : now(),
            ]);
        }

        $orders = $query ? $query->paginate(2)->appends($request->query()) : collect();

        return view('orders.index', compact('orders', 'search', 'startDate', 'endDate'));
    }




    /**
     * Création d'une commande depuis le formulaire Web
     */
     public function storeWeb(Request $request)
    {
        // Allow quantity to be nullable because some supplies are sold by measure
        $request->validate([
            'mercerie_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.merchant_supply_id' => 'required|exists:merchant_supplies,id',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.measure_requested' => 'nullable|string|max:100'
        ]);

        $user = $request->user();
        if (!$user->isCouturier()) {
            return redirect()->back()->with('error', 'Seuls les couturiers peuvent créer une commande.');
        }

        DB::beginTransaction();

        try {
            $total = 0;
            $itemsData = [];

            $parseMeasureToMeters = function (?string $str) {
                if (empty($str)) return null;
                $s = trim(strtolower($str));
                if (preg_match('/^\s*(\d+(?:[\.,]\d+)?)\s*(m|cm|mm)?\s*$/i', $s, $m)) {
                    $num = (float) str_replace(',', '.', $m[1]);
                    $unit = isset($m[2]) ? strtolower($m[2]) : 'm';
                    if ($unit === 'cm') return $num / 100.0;
                    if ($unit === 'mm') return $num / 1000.0;
                    return $num;
                }
                return null;
            };

            foreach ($request->items as $item) {
                $merchantSupply = MerchantSupply::findOrFail($item['merchant_supply_id']);

                // If this merchant supply is sold by measure, require measure_requested
                $saleMode = $merchantSupply->sale_mode ?? ($merchantSupply->supply->sale_mode ?? 'quantity');

                $price = $merchantSupply->price;
                $subtotal = 0;

                if ($saleMode === 'measure') {
                    $measureStr = $item['measure_requested'] ?? null;
                    $meters = $parseMeasureToMeters((string)$measureStr);
                    if ($meters === null) {
                        return redirect()->back()->with('error', "Mesure invalide pour la fourniture ID {$merchantSupply->id}");
                    }
                    // Check stock (assume stock_quantity stored in same unit e.g. meters)
                    if ($meters > $merchantSupply->stock_quantity) {
                        return redirect()->back()->with('error', "Stock insuffisant pour la fourniture ID {$merchantSupply->id}");
                    }
                    $subtotal = $price * $meters;
                    // decrement stock by meters (works if stock column supports decimals)
                    $merchantSupply->decrement('stock_quantity', $meters);

                    $itemsData[] = [
                        'supply_id' => $merchantSupply->supply_id,
                        // DB schema requires a non-null quantity column; use 0 for measure-based items
                        'quantity' => 0,
                        'measure_requested' => $measureStr,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ];
                } else {
                    $qty = isset($item['quantity']) ? intval($item['quantity']) : 0;
                    if ($qty <= 0) {
                        return redirect()->back()->with('error', "Quantité invalide pour la fourniture ID {$merchantSupply->id}");
                    }
                    if ($qty > $merchantSupply->stock_quantity) {
                        return redirect()->back()->with('error', "Stock insuffisant pour la fourniture ID {$merchantSupply->id}");
                    }
                    $subtotal = $price * $qty;
                    $merchantSupply->decrement('stock_quantity', $qty);

                    $itemsData[] = [
                        'supply_id' => $merchantSupply->supply_id,
                        'quantity' => $qty,
                        'measure_requested' => $item['measure_requested'] ?? null,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ];
                }

                $total += $subtotal;
            }

            $order = Order::create([
                'couturier_id' => $user->id,
                'mercerie_id' => $request->mercerie_id,
                'total_amount' => $total,
                'status' => 'pending',
            ]);

            foreach ($itemsData as $data) {
                $data['order_id'] = $order->id;
                OrderItem::create($data);
            }

            // Notification à la mercerie
            $mercerie = $order->mercerie;
            $mercerie->notify(new NewOrderReceived($order));

            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Commande créée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function storeFromMerchant(Request $request, $mercerieId)
    {
        $user = auth()->user();

        if (!$user->isCouturier()) {
            abort(403, 'Seuls les couturiers peuvent passer des commandes.');
        }

        $items = $request->input('items', []);

        if (empty($items)) {
            return back()->with('error', 'Veuillez sélectionner au moins une fourniture.');
        }

        $total = 0;

        // helper to parse measure strings
        $parseMeasureToMeters = function (?string $str) {
            if (empty($str)) return null;
            $s = trim(strtolower($str));
            if (preg_match('/^\s*(\d+(?:[\.,]\d+)?)\s*(m|cm|mm)?\s*$/i', $s, $m)) {
                $num = (float) str_replace(',', '.', $m[1]);
                $unit = isset($m[2]) ? strtolower($m[2]) : 'm';
                if ($unit === 'cm') return $num / 100.0;
                if ($unit === 'mm') return $num / 1000.0;
                return $num;
            }
            return null;
        };

        // Création de la commande
        $order = $user->ordersAsCouturier()->create([
            'mercerie_id' => $mercerieId,
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        // Ajout des éléments de commande
        foreach ($items as $item) {
            if (!isset($item['merchant_supply_id'])) continue;

            $merchantSupply = MerchantSupply::find($item['merchant_supply_id']);
            if (! $merchantSupply) continue;

            $saleMode = $merchantSupply->sale_mode ?? ($merchantSupply->supply->sale_mode ?? 'quantity');

            if ($saleMode === 'measure') {
                $measureStr = $item['measure_requested'] ?? null;
                $meters = $parseMeasureToMeters((string)$measureStr);
                if ($meters === null) {
                    return back()->with('error', 'Mesure invalide pour au moins une fourniture.');
                }
                if ($meters > $merchantSupply->stock_quantity) {
                    return back()->with('error', 'Stock insuffisant pour au moins une fourniture.');
                }
                $subtotal = $merchantSupply->price * $meters;

                $order->items()->create([
                    'merchant_supply_id' => $merchantSupply->id,
                    // DB schema requires a non-null quantity column; use 0 for measure-based items
                    'quantity' => 0,
                    'measure_requested' => $measureStr,
                    'price' => $merchantSupply->price,
                    'subtotal' => $subtotal,
                ]);

                $merchantSupply->decrement('stock_quantity', $meters);
                $total += $subtotal;
            } else {
                $qty = isset($item['quantity']) ? intval($item['quantity']) : 0;
                if ($qty <= 0) continue;
                if ($qty > $merchantSupply->stock_quantity) {
                    return back()->with('error', 'Stock insuffisant pour au moins une fourniture.');
                }
                $subtotal = $merchantSupply->price * $qty;

                $order->items()->create([
                    'merchant_supply_id' => $merchantSupply->id,
                    'quantity' => $qty,
                    'measure_requested' => $item['measure_requested'] ?? null,
                    'price' => $merchantSupply->price,
                    'subtotal' => $subtotal,
                ]);

                $merchantSupply->decrement('stock_quantity', $qty);
                $total += $subtotal;
            }
        }

        // Mise à jour du total
        $order->update(['total_amount' => $total]);

        // Notification à la mercerie
        try {
            $mercerie = $order->mercerie;
            if ($mercerie) {
                $notification = new NewOrderReceived($order);
                $mercerie->notify($notification);
                
                // 🔥 ENVOYER LES NOTIFICATIONS WEB PUSH À LA MERCERIE
                $notification->sendWebPushNotification($mercerie);
            }
        } catch (\Exception $e) {
            logger()->error('Erreur lors de l\'envoi de la notification NewOrderReceived: ' . $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Commande effectuée avec succès.');
    }

    /**
     * 📦 Afficher le détail d’une commande spécifique
     */
    public function show($id)
    {
        $order = Order::with(['items.merchantSupply', 'mercerie', 'couturier'])
            ->findOrFail($id);

        // Vérifier que l'utilisateur est autorisé à la voir
        if (auth()->id() !== $order->couturier_id && auth()->id() !== $order->mercerie_id) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }

    public function preview(Request $request, $mercerieId)
    {
        $mercerie = \App\Models\User::findOrFail($mercerieId);

        if (!$mercerie->isMercerie()) {
            return redirect()->back()->with('error', 'L’utilisateur sélectionné n’est pas une mercerie valide.');
        }

        // Debug: log incoming items payload to help diagnose missing measure_requested values
        $rawItems = $request->items ?? [];
        try {
            logger()->debug('OrderController::preview incoming items', ['items' => $rawItems]);
            // Also log the full request shape (info level) to capture keys and indices
            logger()->info('OrderController::preview full request', ['request' => $request->all()]);
        } catch (\Exception $e) {
            // ignore logging errors
        }
        $items = collect($rawItems)->map(function($item) {
            // Normalize: accept multiple possible measure keys coming from various JS/forms
            $qty = $item['quantity'] ?? null;
            $measure = null;
            // common variants
            foreach (['measure_requested', 'measure', 'mesure', 'measureRequest'] as $k) {
                if (isset($item[$k]) && strlen(trim((string)$item[$k])) > 0) {
                    $measure = trim((string)$item[$k]);
                    break;
                }
            }

            // If measure not provided but quantity contains letters (e.g. '2.5m'), move it
            if (empty($measure) && is_string($qty) && preg_match('/[a-z]/i', $qty)) {
                $measure = trim($qty);
                $qty = null;
            }

            return array_merge($item, ['quantity' => $qty, 'measure_requested' => $measure]);
        })->filter(function ($item) {
            $hasQty = isset($item['quantity']) && intval($item['quantity']) > 0;
            $hasMeasure = isset($item['measure_requested']) && strlen(trim((string)($item['measure_requested'] ?? ''))) > 0;
            return $hasQty || $hasMeasure;
        });

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Veuillez sélectionner au moins une fourniture.');
        }

        // helper: parse measure string into meters (float). supports m, cm, mm. returns null if not numeric
        $parseMeasureToMeters = function (?string $str) {
            if (empty($str)) return null;
            $s = trim(strtolower($str));
            // accept comma or dot as decimal separator
            if (preg_match('/^\s*(\d+(?:[\.,]\d+)?)\s*(m|cm|mm)?\s*$/i', $s, $m)) {
                $num = (float) str_replace(',', '.', $m[1]);
                $unit = isset($m[2]) ? strtolower($m[2]) : 'm';
                if ($unit === 'cm') return $num / 100.0;
                if ($unit === 'mm') return $num / 1000.0;
                // default m
                return $num;
            }
            return null;
        };

        $details = $items->map(function ($item) use ($parseMeasureToMeters) {
            $supply = \App\Models\MerchantSupply::with('supply')->find($item['merchant_supply_id']);
            if (! $supply) return null;

            // Determine sale mode (merchant override preferred)
            $saleMode = $supply->sale_mode ?? $supply->supply->sale_mode ?? 'quantity';

            $price = $supply->price;
            $sous_total = 0;
            // Accept quantity only when it's strictly numeric. This prevents strings like "2.5m"
            // from being coerced to integers and shown in the quantity column.
            $quantity = (isset($item['quantity']) && (is_int($item['quantity']) || is_numeric($item['quantity']))) ? intval($item['quantity']) : null;
            $measureRequested = isset($item['measure_requested']) ? trim($item['measure_requested']) : null;

            if ($saleMode === 'measure') {
                // parse measure into meters and compute subtotal = price * meters
                $meters = $parseMeasureToMeters((string)$measureRequested);
                $sous_total = ($meters !== null) ? ($price * $meters) : 0;
            } else {
                $qty = $quantity ?? 0;
                $sous_total = $price * $qty;
            }

            return [
                'merchant_supply_id' => $supply->id,
                'supply' => $supply->supply->name,
                'quantity' => $quantity,
                'measure_requested' => $measureRequested ?? null,
                'price' => $price,
                'subtotal' => $sous_total,
            ];
        })->filter();

        $total = $details->sum('subtotal');

        return view('orders.preview', compact('mercerie', 'details', 'total'));
    }



     public function accept($id)
    {
        $order = Order::with(['items.merchantSupply.supply'])->findOrFail($id);
        $user = auth()->user();

        if ($order->mercerie_id !== $user->id) {
            abort(403, 'Action non autorisée.');
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Cette commande a déjà été traitée.');
        }

        // helper: parse measure string into meters (float). supports m, cm, mm. returns null if not numeric
        $parseMeasureToMeters = function (?string $str) {
            if (empty($str)) return null;
            $s = trim(strtolower($str));
            if (preg_match('/^\s*(\d+(?:[\.,]\d+)?)\s*(m|cm|mm)?\s*$/i', $s, $m)) {
                $num = (float) str_replace(',', '.', $m[1]);
                $unit = isset($m[2]) ? strtolower($m[2]) : 'm';
                if ($unit === 'cm') return $num / 100.0;
                if ($unit === 'mm') return $num / 1000.0;
                return $num;
            }
            return null;
        };

        DB::beginTransaction();

        try {
            // First pass: validate availability (handle measure-based items)
            foreach ($order->items as $item) {
                $merchantSupply = $item->merchantSupply;
                $saleMode = $merchantSupply->sale_mode ?? ($merchantSupply->supply->sale_mode ?? 'quantity');

                if ($saleMode === 'measure') {
                    $measureStr = $item->measure_requested ?? null;
                    $meters = $parseMeasureToMeters((string) $measureStr);
                    if ($meters === null) {
                        $supplyName = $merchantSupply->supply->name ?? 'Fourniture inconnue';
                        throw new \Exception("Mesure invalide pour '{$supplyName}'.");
                    }
                    if ($merchantSupply->stock_quantity < $meters) {
                        $supplyName = $merchantSupply->supply->name ?? 'Fourniture inconnue';
                        throw new \Exception("Stock insuffisant pour '{$supplyName}'. Stock disponible: {$merchantSupply->stock_quantity}, Mesure demandée: {$meters}");
                    }
                } else {
                    if ($merchantSupply->stock_quantity < $item->quantity) {
                        $supplyName = $merchantSupply->supply->name ?? 'Fourniture inconnue';
                        throw new \Exception("Stock insuffisant pour '{$supplyName}'. Stock disponible: {$merchantSupply->stock_quantity}, Quantité demandée: {$item->quantity}");
                    }
                }
            }

            // Second pass: decrement stock accordingly and notify low stock
            foreach ($order->items as $item) {
                $merchantSupply = $item->merchantSupply;
                $saleMode = $merchantSupply->sale_mode ?? ($merchantSupply->supply->sale_mode ?? 'quantity');

                if ($saleMode === 'measure') {
                    $measureStr = $item->measure_requested ?? null;
                    $meters = $parseMeasureToMeters((string) $measureStr);
                    // decrement by meters (works if stock_quantity is decimal)
                    $merchantSupply->decrement('stock_quantity', $meters);
                    // refresh model to get latest stock value
                    $merchantSupply->refresh();

                    if ($merchantSupply->stock_quantity <= 5) {
                        $user->notify(new LowStockAlert($merchantSupply));
                    }
                } else {
                    $merchantSupply->decrement('stock_quantity', $item->quantity);
                    $merchantSupply->refresh();

                    // Notification stock faible si nécessaire
                    if ($merchantSupply->stock_quantity <= 5) {
                        $user->notify(new LowStockAlert($merchantSupply));
                    }
                }
            }

            $order->update(['status' => 'confirmed']);

            // Notification au couturier
            $notification = new OrderAccepted($order);
            $order->couturier->notify($notification);

            // 🔥 ENVOYER LES NOTIFICATIONS WEB PUSH
            $notification->sendWebPushNotification($order->couturier);

            DB::commit();

            return back()->with('success', 'Commande acceptée et stock mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de l'acceptation de la commande", [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
 * Vérifie la disponibilité du stock pour une commande
 */
private function checkStockAvailability(Order $order)
{
    $unavailableItems = [];
    
    foreach ($order->items as $item) {
        $merchantSupply = $item->merchantSupply;
        
        if ($merchantSupply->stock_quantity < $item->quantity) {
            $unavailableItems[] = [
                'supply_name' => $merchantSupply->supply->name,
                'available' => $merchantSupply->stock_quantity,
                'requested' => $item->quantity
            ];
        }
    }
    
    return $unavailableItems;
}



public function reject($id)
{
    $order = Order::findOrFail($id);
    $user = auth()->user();

    if ($order->mercerie_id !== $user->id) {
        abort(403, 'Action non autorisée.');
    }

    if ($order->status !== 'pending') {
        return back()->with('error', 'Cette commande a déjà été traitée.');
    }

    $order->update(['status' => 'cancelled']);

    // Notification au couturier
    $notification = new OrderRejected($order);
    $order->couturier->notify($notification);

    // 🔥 ENVOYER LES NOTIFICATIONS WEB PUSH
    $notification->sendWebPushNotification($order->couturier);

    return back()->with('success', 'Commande rejetée avec succès.');
}



}
