<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItem extends Model
{
    /** @use HasFactory<\Database\Factories\BorrowedItemFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'expected_return_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemManagement::class, 'item_id');
    }

    public function borrowRequest()
    {
        return $this->belongsTo(BorrowRequest::class, 'borrow_request_id');
    }
}

