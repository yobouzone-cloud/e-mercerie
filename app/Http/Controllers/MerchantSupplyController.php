<?php

namespace App\Http\Controllers;

use App\Models\MerchantSupply;
use App\Models\Supply;
use Illuminate\Http\Request;

class MerchantSupplyController extends Controller
{
    // Affiche toutes les fournitures du marchand
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = MerchantSupply::with('supply')
            ->where('user_id', $request->user()->id)
            ->latest();

        if ($search) {
            $query->whereHas('supply', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $merchantSupplies = $query->paginate(1)->withQueryString();

        // If AJAX request, return rendered partials for rows and pagination
        if ($request->ajax()) {
            $rows = view('merchant.supplies._rows', compact('merchantSupplies'))->render();
            $pagination = view('merchant.supplies._pagination', compact('merchantSupplies'))->render();
            return response()->json(['rows' => $rows, 'pagination' => $pagination]);
        }

        return view('merchant.supplies.index', compact('merchantSupplies', 'search'));
    }

    // Formulaire pour ajouter une nouvelle fourniture
    public function create(Request $request)
    {
        $user = $request->user();

        if (!$user->city || !$user->phone || !$user->address) {
            return redirect()->route('merchant.supplies.index')
                ->with('showProfileModal', true);
        }

        // Charger seulement les fournitures nécessaires pour l'initialisation
        $supplies = Supply::limit(50)->get();
        return view('merchant.supplies.create', compact('supplies'));
    }

    // Ajoutez cette méthode pour la recherche AJAX
    public function searchSupplies(Request $request)
    {
        $search = $request->get('q');
        $supplies = Supply::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
            ->limit(20)
            ->get(['id', 'name as text']);

        return response()->json(['results' => $supplies]);
    }

    // Stocke la nouvelle fourniture
    public function store(Request $request)
    {
        $data = $request->validate([
            'supply_id' => 'required|exists:supplies,id',
            'price' => 'required|numeric|min:0',
            // allow decimal stock quantities for measured supplies
            'stock_quantity' => 'required|numeric|min:0',
            // 'measure' and 'sale_mode' are admin-only and must not be provided by merchants
            // reject any attempt by a merchant to set these fields
            'sale_mode' => 'prohibited',
            'measure' => 'prohibited',
        ]);

        $existing = MerchantSupply::where('user_id', $request->user()->id)
            ->where('supply_id', $data['supply_id'])
            ->first();

        // Get admin-level defaults from the Supply model
        $supply = Supply::find($data['supply_id']);
        $adminSaleMode = $supply->sale_mode ?? 'quantity';
        $adminMeasure = $supply->measure ?? null;

        if ($existing) {
            // update only merchant-editable fields
            $existing->update([
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
            ]);

            // Ensure merchant record has a sale_mode/measure; do not overwrite if already set
            $dirty = false;
            if (empty($existing->sale_mode)) { $existing->sale_mode = $adminSaleMode; $dirty = true; }
            if (empty($existing->measure) && $adminMeasure) { $existing->measure = $adminMeasure; $dirty = true; }
            if ($dirty) { $existing->save(); }

            return redirect()->route('merchant.supplies.index')
                ->with('success', 'Fourniture déjà existante, mise à jour avec succès');
        }

        // Create merchant supply, then explicitly set admin-defined sale_mode/measure
        $merchantSupply = MerchantSupply::create([
            'user_id' => $request->user()->id,
            'supply_id' => $data['supply_id'],
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'],
        ]);

        // Assign admin-defined sale_mode/measure after creation (avoid mass-assigning these via fillable)
        $merchantSupply->sale_mode = $adminSaleMode;
        if ($adminMeasure) $merchantSupply->measure = $adminMeasure;
        $merchantSupply->save();

        return redirect()->route('merchant.supplies.index')
            ->with('success', 'Fourniture ajoutée à votre boutique');
    }

    // Formulaire pour éditer une fourniture
    public function edit($id, Request $request)
    {
        $merchantSupply = MerchantSupply::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $supplies = Supply::all();
        return view('merchant.supplies.edit', compact('merchantSupply', 'supplies'));
    }

    // Mise à jour d'une fourniture
    public function update(Request $request, $id)
    {
        $merchantSupply = MerchantSupply::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $data = $request->validate([
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            // merchants cannot change measure or sale_mode
            // explicitly prohibit these fields if submitted
            'sale_mode' => 'prohibited',
            'measure' => 'prohibited',
        ]);

        // Only update mutable fields for merchant
        $merchantSupply->update([
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'],
        ]);

        return redirect()->route('merchant.supplies.index')
            ->with('success', 'Fourniture mise à jour avec succès');
    }

    // Suppression d'une fourniture
    public function destroy(Request $request, $id)
    {
        $merchantSupply = MerchantSupply::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $merchantSupply->delete();

        return redirect()->route('merchant.supplies.index')
            ->with('success', 'Fourniture supprimée avec succès');
    }
}
