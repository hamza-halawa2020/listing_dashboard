<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'post_id',
        'comment',
        'status',
        'guest_name',
        'guest_phone',
        'approved_by',
        'created_by',
    ];

    protected function isGuest(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => blank($this->created_by),
        );
    }

    protected function authorName(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->createdBy?->name ?? $this->guest_name,
        );
    }

    protected function authorPhone(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->createdBy?->phone ?? $this->guest_phone,
        );
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->approvedBy();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
