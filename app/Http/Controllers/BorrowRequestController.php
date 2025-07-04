<?php

namespace App\Http\Controllers;
use App\Models\BorrowRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class BorrowRequestController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'item_id' => 'required|exists:items,id',
        'quantity' => 'required|integer|min:1',
        'expected_return' => 'required|date',
    ]);

    BorrowRequest::create([
        'user_id' => Auth::id(),
        'item_id' => $request->item_id,
        'quantity' => $request->quantity,
        'expected_return' => $request->expected_return,
    ]);

    return redirect()->back()->with('message', 'Borrow request sent!');
}

public function index()
{
    $borrowedItems = BorrowRequest::with(['item', 'user'])->get();

    return inertia('BorrowRequest', [
        'borrowedItems' => $borrowedItems,
    ]);
}

public function updateStatus(Request $request, $id)
{
    $request->validate(['status' => 'required|in:accepted,rejected']);

    $borrow = BorrowRequest::findOrFail($id);
    $borrow->status = $request->status;
    $borrow->save();

    return redirect()->back();
}
}
