<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowedHistory extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'expected_return_date',
        'status',
        'borrowed_item_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemManagement::class, 'item_id');
    }

    public function borrowedItem()
    {
        return $this->belongsTo(BorrowedItem::class);
    }
}
