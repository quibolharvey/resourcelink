<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purchase_cart_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseCart()
    {
        return $this->belongsTo(PurchaseCart::class);
    }
} 