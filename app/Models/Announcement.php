<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'priority',
        'status',
        'published_at',
        'expires_at',
        'target_roles',
        'is_pinned',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'target_roles' => 'array',
        'is_pinned' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeForRole($query, $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $role);
        });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderByRaw("CASE priority 
            WHEN 'urgent' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'medium' THEN 3 
            WHEN 'low' THEN 4 
            END");
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at->isPast() && 
               !$this->isExpired();
    }
}
