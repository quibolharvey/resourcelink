<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Inventory::where('user_id', auth()->id())->get();
        return Inertia::render('Inventory', ['items' => $items]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit' => 'required|string',
            'description' => 'required|string',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
        ]);
        $validated['user_id'] = auth()->id();

        Inventory::create($validated);

        return redirect()->back()->with('success', 'Item added successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'unit' => 'required|string',
            'description' => 'required|string',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
        ]);

        $inventory->update($validated);

        return redirect()->back()->with('success', 'Item Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);
        // Optional: ensure only owner or admin can delete
        if ($item->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        $item->delete();
        return redirect()->back()->with('success', 'Item deleted successfully.');
    }

    /**
     * Pull out (reduce quantity) of an inventory item.
     */
    public function pullOut(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Inventory::findOrFail($id);
        if ($item->quantity < $validated['quantity']) {
            $item->quantity = 0;
            $item->save();
            return redirect()->back()->with('success', 'Item Pullout Success');
        }
        $item->quantity -= $validated['quantity'];
        $item->save();
        // dd($item->quantity);

        return redirect()->back()->with('success', 'Item Pullout Success');
    }

    public function officesInventory()
    {

        $user = auth()->user();

        if(!$user->hasRole('admin')){
            return redirect()->back();
        }

        $items = \App\Models\Inventory::with('user')->get();
        return \Inertia\Inertia::render('OfficesInventory', [
            'items' => $items,
        ]);
    }
}