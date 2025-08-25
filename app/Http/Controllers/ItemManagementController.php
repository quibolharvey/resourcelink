<?php

namespace App\Http\Controllers;

use App\Models\ItemManagement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;


class ItemManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if(!$user->hasRole('admin')){
            return redirect()->back();
        }

        $items = ItemManagement::latest()->get();
        return Inertia::render('ItemManagement', ['items' => $items]);
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
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'fee' => 'required|numeric|min:0',
        'image' => 'nullable|image|max:2048',
    ]);

        $imagePath = null;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('items', 'public');
    }

    ItemManagement::create([
        'name' => $validated['name'],
        'quantity' => $validated['quantity'],
        'fee' => $validated['fee'],
        'image' => $imagePath,
    ]);

    return redirect()->route('itemmanagement')->with('success', 'Item added successfully.');
}

    /**
     * Display the specified resource.
     */
    public function show(ItemManagement $itemManagement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemManagement $itemManagement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $item = ItemManagement::findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string',
        'quantity' => 'required|integer',
        'fee' => 'required|numeric',
        'image' => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('items', 'public');
    } else {
        unset($validated['image']); // keep old image
    }

    $item->update($validated);

    return redirect()->back();
}

    /**
     * Remove the specified resource from storage.
     */
        public function destroy($id)
    {
        $item = ItemManagement::findOrFail($id);
        $item->delete();

        return redirect()->back();
    }

    public function showAll()
    {

        // $user = auth()->user();

        // if(!$user->hasRole('user')){

        //     return redirect()->back();
        // }
        $items = \App\Models\ItemManagement::all();
        return \Inertia\Inertia::render('Items', ['items' => $items]);
    }
}
