<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseCart;
use App\Models\PurchaseCartItem;
use App\Models\OfficeRequest;

class PurchaseCartController extends Controller
{
    // Get current user's cart and items
    public function getCart()
    {
        $cart = PurchaseCart::firstOrCreate(
            ['user_id' => Auth::id(), 'status' => 'draft'],
            ['status' => 'draft']
        );
        $cart->load('items');
        return response()->json($cart);
    }

    // Add item to cart (from inventory or custom)
    public function addItem(Request $request)
    {
        $cart = PurchaseCart::firstOrCreate(
            ['user_id' => Auth::id(), 'status' => 'draft'],
            ['status' => 'draft']
        );
        $item = $cart->items()->create([
            'inventory_item_id' => $request->input('inventory_item_id'),
            'unit' => $request->input('unit'),
            'description' => $request->input('description'),
            'quantity' => $request->input('quantity'),
            'price' => $request->input('price'),
        ]);
        return response()->json($item);
    }

    // Remove item from cart
    public function removeItem($itemId)
    {
        $item = PurchaseCartItem::findOrFail($itemId);
        if ($item->cart->user_id !== Auth::id() || $item->cart->status !== 'draft') {
            abort(403);
        }
        $item->delete();
        return response()->json(['success' => true]);
    }

    // Submit cart (finalize purchase request)
    public function submitCart()
    {
        $cart = PurchaseCart::where('user_id', Auth::id())->where('status', 'draft')->firstOrFail();
        $cart->status = 'submitted';
        $cart->save();
        // Create OfficeRequest
        OfficeRequest::create([
            'user_id' => Auth::id(),
            'purchase_cart_id' => $cart->id,
        ]);
        // Optionally, trigger further logic (e.g., notify admin)
        return response()->json(['success' => true, 'redirect' => '/officerequest']);
    }
} 