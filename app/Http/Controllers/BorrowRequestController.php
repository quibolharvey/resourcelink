<?php

namespace App\Http\Controllers;
use App\Models\BorrowRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Models\BorrowedItem;
use App\Models\BorrowedHistory;
use App\Models\ItemManagement;

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
    $borrowedItems = BorrowRequest::with(['item', 'user'])->where('status', 'pending')->get();

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

    // If accepted, create a BorrowedItem and a BorrowedHistory
    if ($request->status === 'accepted') {
        BorrowedItem::create([
            'user_id' => $borrow->user_id,
            'item_id' => $borrow->item_id,
            'quantity' => $borrow->quantity,
            'expected_return_date' => $borrow->expected_return,
        ]);
        BorrowedHistory::create([
            'user_id' => $borrow->user_id,
            'item_id' => $borrow->item_id,
            'quantity' => $borrow->quantity,
            'expected_return_date' => $borrow->expected_return,
            'status' => $borrow->status,
        ]);
        // Deduct the borrowed quantity from the item's quantity
        $item = ItemManagement::find($borrow->item_id);
        if ($item) {
            $item->quantity = max(0, $item->quantity - $borrow->quantity);
            $item->save();
        }
    }

    return redirect()->back();
}
}
