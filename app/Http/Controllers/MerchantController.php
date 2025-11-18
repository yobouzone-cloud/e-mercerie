<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Supply;

class MerchantController extends Controller
{
    // Liste de toutes les merceries
    public function index(Request $request)
    {
        $search = $request->input('search');

        $merceries = User::where('role', 'mercerie')
            ->where('id', '!=', auth()->id()) // exclure la mercerie connectée
            ->whereHas('merchantSupplies')   // au moins une fourniture
            ->when($search, function ($query, $search) {
                                $query->where(function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%")
                                            ->orWhere('phone', 'like', "%{$search}%");

                                        // Search by related city name
                    $q->orWhereHas('cityModel', function ($qc) use ($search) {
                        $qc->where('name', 'like', "%{$search}%");
                    });

                    // Search by related quarter name
                    $q->orWhereHas('quarter', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%");
                    });
                                });
            })
            ->with(['merchantSupplies','cityModel','quarter'])
            ->get();

        return view('couturier.merceries.index', compact('merceries', 'search'));
    }

    /**
     * Public landing page showing merceries with supplies
     */
    public function landing(Request $request)
    {
        $search = $request->input('search');

        $merceries = User::where('role', 'mercerie')
            ->whereHas('merchantSupplies')
            ->when(auth()->check(), function ($q) {
                $q->where('id', '!=', auth()->id());
            })
            ->when($search, function ($query, $search) {
                                $query->where(function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                            ->orWhere('phone', 'like', "%{$search}%");

                    // Search by related city name
                    $q->orWhereHas('cityModel', function ($qc) use ($search) {
                        $qc->where('name', 'like', "%{$search}%");
                    });

                    // Search by related quarter name
                    $q->orWhereHas('quarter', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%");
                    });
                                });
            })
            ->with(['merchantSupplies','cityModel','quarter'])
            ->get();

    // Also pass available supplies so landing can show selection form
    // Use pagination to avoid rendering thousands of supplies on the landing page
    $perPage = 5;
    $supplies = Supply::orderBy('name')->paginate($perPage)->withQueryString();
    return view('landing', compact('merceries', 'search', 'supplies'));
    }

    public function searchAjax(Request $request)
    {
        $query = $request->input('search');

        $merceries = User::where('role', 'mercerie')
            // Only merceries (role) that have supplies
            ->whereHas('merchantSupplies')
            // Exclude currently authenticated merchant if present
            ->when(auth()->check(), function ($q) {
                $q->where('id', '!=', auth()->id());
            })
            // Apply search filter when provided
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");

                        // Search by related city name
                        $sub->orWhereHas('cityModel', function ($qc) use ($query) {
                            $qc->where('name', 'like', "%{$query}%");
                        });

                        // Search by related quarter name
                        $sub->orWhereHas('quarter', function ($qq) use ($query) {
                            $qq->where('name', 'like', "%{$query}%");
                        });
                });
            })
            ->with(['merchantSupplies','cityModel','quarter'])
            ->get();

        // Map response to include avatar_url and a short description
        $payload = $merceries->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'city' => $m->city,
                'quarter' => $m->quarter?->name ?? null,
                'phone' => $m->phone,
                'avatar_url' => $m->avatar_url ?? asset('images/defaults/mercerie-avatar.png'),
                'description' => $m->address ? 
                    (strlen($m->address) > 80 ? substr($m->address, 0, 77) . '...' : $m->address) : '',
                'has_supplies' => $m->merchantSupplies->isNotEmpty(),
            ];
        });

        return response()->json($payload);
    }




    // Détails d'une mercerie + fournitures disponibles
    public function show($id)
    {
        $mercerie = User::where('role', 'mercerie')->with('merchantSupplies.supply')->findOrFail($id);
        return view('couturier.merceries.show', compact('mercerie'));
    }

    public function edit()
    {
        $user = auth()->user();
        // Load cities and (optionally) quarters for the user's city to prepopulate selects
        $cities = \App\Models\City::orderBy('name')->get();
        $quarters = collect();
        if (! empty($user->city_id)) {
            $quarters = \App\Models\Quarter::where('city_id', $user->city_id)->orderBy('name')->get();
        }

        // Serve role-specific edit view if exists, else fall back to a generic profile edit
        if ($user->isMercerie()) {
            return view('merceries.profile.edit', ['mercerie' => $user, 'cities' => $cities, 'quarters' => $quarters]);
        }

        // Couturier view
        return view('couturier.profile.edit', ['user' => $user, 'cities' => $cities, 'quarters' => $quarters]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // Role-specific validation: merceries must provide city/quarter; couturiers may not
        if ($user->isMercerie()) {
            $rules = [
                'city_id' => 'required|exists:cities,id',
                'quarter_id' => 'required|exists:quarters,id',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
        } else {
            // Couturier or other roles
            $rules = [
                'name' => 'required|string|max:255',
                'city_id' => 'nullable|exists:cities,id',
                'quarter_id' => 'nullable|exists:quarters,id',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
        }

        $data = $request->validate($rules);

        // If merchant provided both city and quarter, ensure relation integrity
        if ($user->isMercerie() && !empty($data['city_id']) && !empty($data['quarter_id'])) {
            $belongs = \App\Models\Quarter::where('id', $data['quarter_id'])->where('city_id', $data['city_id'])->exists();
            if (! $belongs) {
                return redirect()->back()->withInput()->with('error', 'Le quartier sélectionné n\'appartient pas à la ville choisie.');
            }
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        // Map city_id/quarter_id to user's columns
        $user->city_id = $data['city_id'] ?? null;
        $user->quarter_id = $data['quarter_id'] ?? null;
        $user->phone = $data['phone'] ?? $user->phone;
        $user->address = $data['address'] ?? $user->address;
        if (isset($data['avatar'])) {
            $user->avatar = $data['avatar'];
        }
        $user->save();

        // Redirect depending on role
        if ($user->isMercerie()) {
            return redirect()->route('merchant.supplies.index')->with('success', 'Profil complété avec succès.');
        }

        return redirect()->route('supplies.selection')->with('success', 'Profil mis à jour avec succès.');
    }

}
