<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'status',
        'views_count',
        'created_by',
    ];

    /**
     * If the stored value is a bare filename (no slashes), resolve it to its
     * actual relative path on the public disk so Filament ImageColumn/ImageEntry
     * can build the correct URL.
     */
    public function getImageAttribute(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        // Already a path or URL — return as-is
        if (str_contains($value, '/') || str_starts_with($value, 'http')) {
            return $value;
        }

        // Bare filename: search the disk for it
        $found = collect(Storage::disk('public')->allFiles())
            ->first(fn ($f) => basename($f) === $value);

        return $found ?? $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
