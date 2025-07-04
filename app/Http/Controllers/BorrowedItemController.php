<?php

namespace App\Http\Controllers;

use App\Models\BorrowedItem;
use Illuminate\Http\Request;

class BorrowedItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    $request->validate([
        'item_id' => 'required|exists:item_management,id',
        'quantity' => 'required|integer|min:1',
        'expected_return_date' => 'required|date|after_or_equal:today',
    ]);

    BorrowedItem::create([
        'user_id' => auth()->id(),
        'item_id' => $request->item_id,
        'quantity' => $request->quantity,
        'expected_return_date' => $request->expected_return_date,
    ]);

    return redirect()->route('borrow.request')->with('success', 'Item borrowed successfully.');
}
public function showRequests()
{
    $borrowedItems = BorrowedItem::with('user', 'item')->get();

    return Inertia::render('BorrowRequest', [
        'borrowedItems' => $borrowedItems,
    ]);
}
public function showBorrowRequests()
{
    $borrowedItems = \App\Models\BorrowedItem::with(['user', 'item'])->get();

    return \Inertia\Inertia::render('BorrowRequest', [
        'borrowedItems' => $borrowedItems
    ]);
}


public function showAll()
{
    $borrowedItems = BorrowedItem::with('user', 'item')->get();

    return Inertia::render('BorrowedItems', [
        'borrowedItems' => $borrowedItems,
    ]);
}



    /**
     * Display the specified resource.
     */
    public function show(BorrowedItem $borrowedItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BorrowedItem $borrowedItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BorrowedItem $borrowedItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BorrowedItem $borrowedItem)
    {
        //
    }
}
