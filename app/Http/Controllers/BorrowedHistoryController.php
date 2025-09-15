<?php

namespace App\Http\Controllers;

use App\Models\BorrowedHistory;
use App\Models\BorrowedItem;
use App\Models\BorrowRequest;
use App\Models\ItemManagement;
use Illuminate\Http\Request;

class BorrowedHistoryController extends Controller
{
    public function index()
    {
        $borrowedHistories = BorrowedHistory::with(['user', 'item', 'borrowedItem'])->get();
        return inertia('BorrowHistory', [
            'borrowedHistories' => $borrowedHistories,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:returned,overdue']);
        $history = \App\Models\BorrowedHistory::with('borrowedItem')->findOrFail($id);
        
        // Prevent status changes if the item is already returned
        if ($history->status === 'returned') {
            return redirect()->back()->with('error', 'Cannot change status of returned items.');
        }
        
        $previousStatus = $history->status;
        $history->status = $request->status;
        $history->save();

        // Update the corresponding BorrowedItem status using the direct relationship
        if ($history->borrowedItem) {
            $history->borrowedItem->status = $request->status;
            $history->borrowedItem->save();
        }

        // Sync the corresponding borrow request status so admin view updates
        // Find the specific BorrowRequest that corresponds to this BorrowedHistory record
        $borrowRequest = BorrowRequest::where('user_id', $history->user_id)
            ->where('item_id', $history->item_id)
            ->where('expected_return', $history->expected_return_date)
            ->where('quantity', $history->quantity)
            ->first();
            
        if ($borrowRequest) {
            $borrowRequest->status = $request->status;
            $borrowRequest->save();
        }

        // If status is 'returned' and it wasn't already returned, add the borrowed quantity back to the item's stock
        if ($request->status === 'returned' && $previousStatus !== 'returned') {
            $item = ItemManagement::find($history->item_id);
            if ($item) {
                $item->quantity += $history->quantity;
                $item->save();
            }
        }

        return redirect()->back();
    }
}
