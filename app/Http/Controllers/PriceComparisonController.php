<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PriceComparisonController extends Controller
{
    // Étape 3 : Comparaison des merceries pour Blade
    public function compare(Request $request)
    {
        // Validate optional location filters
        $data = $request->validate([
            'city_id' => 'nullable|exists:cities,id',
            'quarter_id' => 'nullable|exists:quarters,id',
        ]);

        // If both provided, ensure the quarter belongs to the city
        if (!empty($data['city_id']) && !empty($data['quarter_id'])) {
            $belongs = \App\Models\Quarter::where('id', $data['quarter_id'])->where('city_id', $data['city_id'])->exists();
            if (! $belongs) {
                return redirect()->back()->with('error', 'Le quartier sélectionné n\'appartient pas à la ville choisie.');
            }
        }
    $itemsInput = $request->input('items', []);
        $items = [];

        foreach ($itemsInput as $supplyId => $itemData) {
            // Support either quantity or measure_requested; if both provided keep both
            if (!empty($itemData['measure_requested'])) {
                $rawMeasure = trim($itemData['measure_requested']);
                // validate measure format server-side (number + optional unit)
                $measureRegex = '/^\s*\d+(?:[\.,]\d+)?\s*(m|cm|mm)?\s*$/i';
                if (!preg_match($measureRegex, $rawMeasure)) {
                    return redirect()->back()->with('error', "Format de mesure invalide pour la fourniture ID {$supplyId}: {$rawMeasure}");
                }

                $entry = [
                    'supply_id' => $supplyId,
                    'measure_requested' => $rawMeasure
                ];

                if (isset($itemData['quantity']) && intval($itemData['quantity']) > 0) {
                    $entry['quantity'] = intval($itemData['quantity']);
                }

                $items[] = $entry;
                continue;
            }

            if (isset($itemData['quantity']) && $itemData['quantity'] > 0) {
                // If quantity looks like a measure string (e.g. '2.5m'), treat it as measure_requested
                $qtyRaw = $itemData['quantity'];
                if (is_string($qtyRaw) && preg_match('/[a-z]/i', $qtyRaw)) {
                    $measureCandidate = trim($qtyRaw);
                    $measureRegex = '/^\s*\d+(?:[\.,]\d+)?\s*(m|cm|mm)?\s*$/i';
                    if (preg_match($measureRegex, $measureCandidate)) {
                        $items[] = [
                            'supply_id' => $supplyId,
                            'measure_requested' => $measureCandidate
                        ];
                        continue;
                    }
                }

                $items[] = [
                    'supply_id' => $supplyId,
                    'quantity' => intval($itemData['quantity'])
                ];
            }
        }

        if (empty($items)) {
            return redirect()->back()->with('error', 'Veuillez sélectionner au moins une fourniture.');
        }

        // Charger les merceries avec leurs fournitures et les infos de la fourniture liée
        $merceriesQuery = User::where('role', 'mercerie')->with('merchantSupplies.supply');

        // Apply optional filters
        if (!empty($data['city_id'])) {
            $merceriesQuery->where('city_id', $data['city_id']);
        }
        if (!empty($data['quarter_id'])) {
            $merceriesQuery->where('quarter_id', $data['quarter_id']);
        }

        $merceries = $merceriesQuery->get();
        $disponibles = [];
        $non_disponibles = [];

        // helper: parse measure string into meters (float). supports m, cm, mm. returns null if not numeric
        $parseMeasureToMeters = function (?string $str) {
            if (empty($str)) return null;
            $s = trim(strtolower($str));
            // accept comma or dot as decimal separator
            if (preg_match('/^\s*(\d+(?:[\.,]\d+)?)\s*(m|cm|mm)?\s*$/i', $s, $m)) {
                $num = (float) str_replace(',', '.', $m[1]);
                $unit = isset($m[2]) && $m[2] ? strtolower($m[2]) : 'm';
                if ($unit === 'cm') return $num / 100.0;
                if ($unit === 'mm') return $num / 1000.0;
                // default m
                return $num;
            }
            return null;
        };

        foreach ($merceries as $mercerie) {
            $total = 0;
            $details = [];
            $peut_fournir = true;
            $raisons = [];

            foreach ($items as $item) {
                $supply = $mercerie->merchantSupplies->firstWhere('supply_id', $item['supply_id']);

                // Récupération du nom de la fourniture depuis la table supplies
                $supplyName = \App\Models\Supply::find($item['supply_id'])->name ?? "Fourniture inconnue";

                if (!$supply) {
                    $peut_fournir = false;
                    $raisons[] = "La fourniture « {$supplyName} » n’est pas disponible chez cette mercerie.";
                    continue;
                }

                // If the requested item is by measure, ensure merchant supplies supports measure
                if (!empty($item['measure_requested'])) {
                    if (($supply->sale_mode ?? 'quantity') !== 'measure') {
                        $peut_fournir = false;
                        $raisons[] = "La fourniture « {$supplyName} » n'est pas vendue par mesure chez cette mercerie.";
                        continue;
                    }

                    // For measure requests, ensure merchant has stock (stock_quantity > 0)
                    if ($supply->stock_quantity <= 0) {
                        $peut_fournir = false;
                        $raisons[] = "⚠️ Stock insuffisant pour « {$supplyName} ».";
                        continue;
                    }

                    // If user provided a quantity along with the measure, multiply by it
                    $quantityMultiplier = isset($item['quantity']) && intval($item['quantity']) > 0 ? intval($item['quantity']) : 1;

                    // parse the measure into meters and compute subtotal = price * meters * quantity
                    $meters = $parseMeasureToMeters((string) $item['measure_requested']);
                    $sous_total = ($meters !== null) ? ($supply->price * $meters * $quantityMultiplier) : 0;
                    $total += $sous_total;

                    $details[] = [
                        'supply' => $supplyName,
                        'prix_unitaire' => (float) $supply->price,
                        'measure_requested' => $item['measure_requested'],
                        'quantite' => ($quantityMultiplier > 1) ? $quantityMultiplier : null,
                        'sous_total' => $sous_total,
                        'merchant_supply_id' => $supply->id,
                    ];

                    continue;
                }

                // Standard quantity handling
                if ($supply->stock_quantity < $item['quantity']) {
                    $peut_fournir = false;
                    $raisons[] = "⚠️ Stock insuffisant pour « {$supplyName} » (disponible : {$supply->stock_quantity}).";
                    continue;
                }

                $sous_total = $supply->price * $item['quantity'];
                $total += $sous_total;

                $details[] = [
                    'supply' => $supplyName,
                    'prix_unitaire' => (float) $supply->price,
                    'quantite' => $item['quantity'],
                    'sous_total' => $sous_total,
                    'merchant_supply_id' => $supply->id,
                ];
            }

            $mercerieInfo = [
                'id' => $mercerie->id,
                'name' => $mercerie->name,
                'city_name' => $mercerie->cityModel?->name ?? null,
                'quarter_name' => $mercerie->quarter?->name ?? null,
            ];

            if ($peut_fournir) {
                $disponibles[] = [
                    'mercerie' => $mercerieInfo,
                    'total_estime' => $total,
                    'details' => $details,
                ];
            } else {
                $non_disponibles[] = [
                    'mercerie' => $mercerieInfo,
                    'raisons' => $raisons
                ];
            }
        }

    // Trier les merceries disponibles par prix total estimé croissant
    usort($disponibles, fn($a, $b) => $a['total_estime'] <=> $b['total_estime']);

    // Attach selected city/quarter (models) for the view to display
    $selectedCity = !empty($data['city_id']) ? \App\Models\City::find($data['city_id']) : null;
    $selectedQuarter = !empty($data['quarter_id']) ? \App\Models\Quarter::find($data['quarter_id']) : null;

    // Store results in session and redirect to GET route (Post-Redirect-Get) so guests can be redirected to this GET URL after login
    $request->session()->put('compare.disponibles', $disponibles);
    $request->session()->put('compare.non_disponibles', $non_disponibles);
    $request->session()->put('compare.items', $items);
    $request->session()->put('compare.selectedCity', $selectedCity);
    $request->session()->put('compare.selectedQuarter', $selectedQuarter);

    return redirect()->route('merceries.compare.show');
    }

    /**
     * Display the comparison results page (GET).
     * If the POST compare() stored results in session, retrieve them; otherwise show empty view.
     */
    public function show(Request $request)
    {
        // Retrieve previously computed results from session without removing them
        $disponibles = $request->session()->get('compare.disponibles', []);
        $non_disponibles = $request->session()->get('compare.non_disponibles', []);
        $items = $request->session()->get('compare.items', []);
        $selectedCity = $request->session()->get('compare.selectedCity', null);
        $selectedQuarter = $request->session()->get('compare.selectedQuarter', null);

        return view('merceries.compare', compact('disponibles', 'non_disponibles', 'items', 'selectedCity', 'selectedQuarter'));
    }

}
