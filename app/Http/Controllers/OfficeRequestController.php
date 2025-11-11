<?php

namespace App\Http\Controllers;

use App\Models\OfficeRequest;
use App\Models\ConsolidatedForm;
use App\Models\ConsolidatedItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class OfficeRequestController extends Controller
{
    public function index()
    {

        $user = auth()->user();

        if(!$user->hasRole('admin')){
            return redirect()->back();
        }

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
        $user = auth()->user();

        if(!$user->hasRole('office')){
            return redirect()->back();
        }

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

    public function consolidate($id, Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $officeRequest = \App\Models\OfficeRequest::with('purchaseCart.items')->findOrFail($id);

        // Optionally validate incoming items payload
        $payload = $request->validate([
            'items' => ['array'],
            'items.*.unit' => ['nullable', 'string'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['nullable'],
            'items.*.price' => ['nullable'],
        ]);

        // Get or create the current admin's draft consolidated form
        $form = ConsolidatedForm::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'draft'],
            ['status' => 'draft']
        );

        // Merge items by unit+description (case-insensitive)
        $incoming = collect($payload['items'] ?? []);
        foreach ($incoming as $item) {
            $unit = $item['unit'] ?? null;
            $description = $item['description'] ?? null;
            $price = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if (!$description || $quantity <= 0) {
                continue;
            }

            $existing = $form->items()
                ->whereRaw('LOWER(description) = ?', [mb_strtolower($description)])
                ->where('unit', $unit)
                ->first();

            if ($existing) {
                $existing->quantity = $existing->quantity + $quantity;
                // Keep the first price set; or update if price is zero
                if ((float) $existing->price <= 0 && $price > 0) {
                    $existing->price = $price;
                }
                $existing->save();
            } else {
                ConsolidatedItem::create([
                    'consolidated_form_id' => $form->id,
                    'unit' => $unit,
                    'description' => $description,
                    'price' => $price,
                    'quantity' => $quantity,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function getConsolidatedForm()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $form = ConsolidatedForm::with('items')
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->first();

        $items = $form ? $form->items->map(function ($it) {
            return [
                'unit' => $it->unit,
                'description' => $it->description,
                'price' => (float) $it->price,
                'quantity' => (int) $it->quantity,
                'total' => (float) $it->price * (int) $it->quantity,
            ];
        })->values()->all() : [];

        $grandTotal = collect($items)->sum(fn ($i) => $i['total']);

        return response()->json([
            'items' => $items,
            'grand_total' => $grandTotal,
        ]);
    }

    public function clearConsolidatedForm()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $form = ConsolidatedForm::where('user_id', $user->id)
            ->where('status', 'draft')
            ->first();
        if ($form) {
            $form->items()->delete();
        }
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $officeRequest = \App\Models\OfficeRequest::with('purchaseCart.items')->findOrFail($id);

        DB::transaction(function () use ($officeRequest) {
            $cart = $officeRequest->purchaseCart;
            if ($cart) {
                // delete related items then cart
                if (method_exists($cart, 'items')) {
                    $cart->items()->delete();
                }
                $cart->delete();
            }
            $officeRequest->delete();
        });

        return response()->json(['success' => true]);
    }
}