<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ListingImage extends Model
{
    protected $fillable = [
        'listing_id',
        'image_path',
        'is_cover',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    /**
     * Normalize image_path: if stored as a full URL, extract the relative path
     * so Filament ImageColumn (disk: public) can display it correctly.
     */
    public function getImagePathAttribute(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        // Already a relative path — return as-is
        if (!str_starts_with($value, 'http')) {
            return $value;
        }

        // Full URL — extract the path after /files/
        if (preg_match('#/files/(.+)$#', $value, $matches)) {
            return $matches[1];
        }

        // Fallback: extract path after /storage/
        if (preg_match('#/storage/(.+)$#', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
