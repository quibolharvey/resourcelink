<?php

namespace App\Http\Controllers;

use App\Models\OfficeRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OfficeRequestController extends Controller
{
    public function index()
    {
        $requests = OfficeRequest::with(['user', 'purchaseCart'])
            ->orderByDesc('created_at')
            ->get();
        return Inertia::render('OfficeRequest', [
            'requests' => $requests,
        ]);
    }

    public function show($id)
    {
        $request = \App\Models\OfficeRequest::with(['user', 'purchaseCart.items'])->findOrFail($id);
        return response()->json($request);
    }

    public function userHistory()
    {
        $userId = auth()->id();
        $requests = \App\Models\OfficeRequest::with(['user', 'purchaseCart'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
        return Inertia::render('RequestHistory', [
            'requests' => $requests,
        ]);
    }

    public function approve($id)
    {
        $officeRequest = \App\Models\OfficeRequest::with('purchaseCart')->findOrFail($id);
        $cart = $officeRequest->purchaseCart;
        $cart->status = 'approved';
        $cart->save();
        return response()->json(['success' => true]);
    }

    public function allHistory()
    {
        $requests = \App\Models\OfficeRequest::with(['user', 'purchaseCart'])
            ->whereHas('purchaseCart', function($query) {
                $query->where('status', 'approved');
            })
            ->orderByDesc('created_at')
            ->get();
        return response()->json($requests);
    }
} 