<?php

namespace App\Http\Controllers;

use App\Models\BorrowedHistory;
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
        return redirect()->back();
    }
}
