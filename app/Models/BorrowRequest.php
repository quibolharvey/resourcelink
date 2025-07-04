<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowRequest extends Model
{
    use HasFactory;

    // ✅ Add all attributes you want to allow for mass assignment
    protected $fillable = [
        'item_id',
        'user_id',     // <-- Add this line
        'quantity',
        'expected_return',
        'status',
    ];

    // Optional relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
