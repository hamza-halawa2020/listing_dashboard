<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'review',
        'rating',
        'status',
        'guest_name',
        'guest_phone',
        'guest_email',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'boolean',
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

    protected function authorEmail(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->createdBy?->email ?? $this->guest_email,
        );
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

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}
