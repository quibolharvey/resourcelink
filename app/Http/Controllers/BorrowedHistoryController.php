<?php

namespace App\Http\Controllers;

use App\Models\BorrowedHistory;
use App\Models\BorrowRequest;
use Illuminate\Http\Request;

class BorrowedHistoryController extends Controller
{
    public function index()
    {
        $borrowedHistories = BorrowedHistory::with(['user', 'item'])->get();
        return inertia('BorrowHistory', [
            'borrowedHistories' => $borrowedHistories,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:returned,overdue']);
        $history = \App\Models\BorrowedHistory::findOrFail($id);
        $history->status = $request->status;
        $history->save();

        // Sync the corresponding borrow request status so admin view updates
        BorrowRequest::where('user_id', $history->user_id)
            ->where('item_id', $history->item_id)
            ->where(function ($q) use ($history) {
                // Prefer exact expected return match if available
                $q->where('expected_return', $history->expected_return_date)
                  ->orWhereIn('status', ['accepted', 'returned', 'overdue']);
            })
            ->orderByDesc('id')
            ->limit(1)
            ->update(['status' => $request->status]);
        return redirect()->back();
    }
}
