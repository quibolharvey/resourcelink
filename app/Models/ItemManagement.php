<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemManagement extends Model
{
    /** @use HasFactory<\Database\Factories\ItemManagementFactory> */
    use HasFactory;

    protected $table = 'items';

    protected $fillable = ['name', 'quantity', 'fee', 'image'];
}
