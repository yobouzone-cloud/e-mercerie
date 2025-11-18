<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supply;

class AdminSupplyController extends Controller
{
    // List all supplies
    public function index()
    {
        $supplies = Supply::orderBy('name')->paginate(20);
        return view('admin.supplies.index', compact('supplies'));
    }

    // Show create form
    public function create()
    {
        return view('admin.supplies.create');
    }

    // Store new supply
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'measure' => 'nullable|string|max:50',
            'sale_mode' => 'nullable|string|in:quantity,measure',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:1024',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);

        // handle uploaded file if present
        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            $path = $request->file('image_file')->store('supplies', 'public');
            // set web-accessible path
            $data['image_url'] = '/storage/' . $path;
        }

        Supply::create($data);

        return redirect()->route('admin.supplies.index')->with('success', 'Fourniture ajoutée.');
    }

    // Show edit form
    public function edit($id)
    {
        $supply = Supply::findOrFail($id);
        return view('admin.supplies.edit', compact('supply'));
    }

    // Update supply
    public function update(Request $request, $id)
    {
        $supply = Supply::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'measure' => 'nullable|string|max:50',
            'sale_mode' => 'nullable|string|in:quantity,measure',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:1024',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);

        // handle uploaded file: store and update image_url, remove old file if it was stored in /storage/supplies
        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            $path = $request->file('image_file')->store('supplies', 'public');
            $newUrl = '/storage/' . $path;

            // delete old file if it's in /storage/supplies
            if (!empty($supply->image_url) && preg_match('#^/storage/supplies/#', $supply->image_url)) {
                $oldPath = str_replace('/storage/', '', $supply->image_url);
                try { \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath); } catch(\Throwable $e) {}
            }

            $data['image_url'] = $newUrl;
        }

        $supply->update($data);

        return redirect()->route('admin.supplies.index')->with('success', 'Fourniture mise à jour.');
    }

    // Destroy supply
    public function destroy($id)
    {
        $supply = Supply::findOrFail($id);
        $supply->delete();
        return redirect()->route('admin.supplies.index')->with('success', 'Fourniture supprimée.');
    }
}
