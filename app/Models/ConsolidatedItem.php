<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsolidatedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'consolidated_form_id',
        'unit',
        'description',
        'price',
        'quantity',
    ];

    public function consolidatedForm(): BelongsTo
    {
        return $this->belongsTo(ConsolidatedForm::class);
    }
}





