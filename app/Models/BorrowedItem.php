<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItem extends Model
{
    /** @use HasFactory<\Database\Factories\BorrowedItemFactory> */
    use HasFactory;

    public function user()
{
    return $this->belongsTo(User::class);
}

public function item()
{
    return $this->belongsTo(ItemManagement::class, 'item_id');
}
}

