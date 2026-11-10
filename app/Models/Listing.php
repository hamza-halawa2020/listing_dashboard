<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Listing extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'location_id',
        'address',
        'description',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function phones()
    {
        return $this->hasMany(ListingPhone::class);
    }

    public function links()
    {
        return $this->hasMany(ListingLink::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function workingHours()
    {
        return $this->hasMany(ListingWorkingHour::class);
    }

    /**
     * Create a full 100% copy of this listing, including all related data
     * (phones, links, images, offers, working hours). Image files are NOT
     * copied on disk — the new listing references the same image paths.
     *
     * @param  string|null  $newName  Override the name (defaults to original + " - نسخة").
     */
    public function createDuplicate(?string $newName = null): self
    {
        return DB::transaction(function () use ($newName) {
            $copy = $this->replicate();
            $copy->name = $newName ?? $this->name . __(' - Copy');
            $copy->save();

            // Phones
            foreach ($this->phones as $phone) {
                $copy->phones()->create([
                    'phone_number' => $phone->phone_number,
                    'type' => $phone->type,
                    'contact_person' => $phone->contact_person,
                ]);
            }

            // Links
            foreach ($this->links as $link) {
                $copy->links()->create([
                    'title' => $link->title,
                    'url' => $link->url,
                ]);
            }

            // Images — reference the same path (no file duplication on disk)
            foreach ($this->images as $image) {
                $copy->images()->create([
                    'image_path' => $image->getRawOriginal('image_path'),
                    'is_cover' => $image->is_cover,
                ]);
            }

            // Offers
            foreach ($this->offers as $offer) {
                $copy->offers()->create([
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'price_before_discount' => $offer->price_before_discount,
                    'price_after_discount' => $offer->price_after_discount,
                    'discount_amount' => $offer->discount_amount,
                    'discount_percentage' => $offer->discount_percentage,
                    'is_active' => $offer->is_active,
                ]);
            }

            // Working hours
            foreach ($this->workingHours as $wh) {
                $copy->workingHours()->create([
                    'day' => $wh->day,
                    'open_time' => $wh->open_time,
                    'close_time' => $wh->close_time,
                    'is_closed' => $wh->is_closed,
                ]);
            }

            return $copy;
        });
    }

    protected static function booted(): void
    {
        static::saving(static function (Listing $listing): void {
            $listing->preventDuplicate();
        });
    }

    private function preventDuplicate(): void
    {
        if (! filled($this->name)
            || ! filled($this->category_id)
            || ! filled($this->location_id)
            || ! filled($this->address)) {
            return;
        }

        $query = static::query()
            ->where('name', $this->name)
            ->where('category_id', $this->category_id)
            ->where('location_id', $this->location_id)
            ->where('address', $this->address);

        if ($this->getKey()) {
            $query->where('id', '<>', $this->getKey());
        }

        if ($query->exists()) {
            throw new RuntimeException(__('listing.duplicate'));
        }
    }
}
