<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_cart_id',
        'inventory_item_id',
        'unit',
        'description',
        'quantity',
        'price',
    ];

    public function cart()
    {
        return $this->belongsTo(PurchaseCart::class, 'purchase_cart_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(Inventory::class, 'inventory_item_id');
    }
} 