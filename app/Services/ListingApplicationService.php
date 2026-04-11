<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingApplication;
use App\Models\ListingPhone;
use App\Models\ListingWorkingHour;
use App\Models\ListingLink;
use App\Models\ListingImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingApplicationService
{
    /**
     * Submit application: Create listing with all data, save application record
     */
    public function submitApplication(array $data): ListingApplication
    {
        // Create the listing (disabled initially)
        $listing = Listing::create([
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'location_id' => $data['location_id'],
            'address' => $data['address'] ?? null,
            'description' => $data['description'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_active' => false,
        ]);

        // Add phones
        if (!empty($data['phones']) && is_array($data['phones'])) {
            foreach ($data['phones'] as $phone) {
                ListingPhone::create([
                    'listing_id' => $listing->id,
                    'phone_number' => $phone['number'],
                    'type' => strtolower($phone['type']),
                    'contact_person' => $data['contact_name'],
                ]);
            }
        }

        // Add working hours
        if (!empty($data['working_hours']) && is_array($data['working_hours'])) {
            foreach ($data['working_hours'] as $hour) {
                ListingWorkingHour::create([
                    'listing_id' => $listing->id,
                    'day' => $hour['day'],
                    'is_closed' => $hour['is_closed'] ?? false,
                    'open_time' => $hour['open_time'] ?? null,
                    'close_time' => $hour['close_time'] ?? null,
                ]);
            }
        }

        // Add links
        if (!empty($data['links']) && is_array($data['links'])) {
            foreach ($data['links'] as $link) {
                ListingLink::create([
                    'listing_id' => $listing->id,
                    'url' => $link['url'],
                    'type' => $link['type'],
                    'title' => $link['type'],
                ]);
            }
        }

        // Add images
        if (!empty($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                $imagePath = $this->saveBase64Image($image['image_path'], $listing->id);
                ListingImage::create([
                    'listing_id' => $listing->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        // Create application record (only contact info)
        $application = ListingApplication::create([
            'listing_id' => $listing->id,
            'contact_name' => $data['contact_name'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'status' => 'pending',
        ]);

        return $application;
    }

    /**
     * Approve application: Activate the listing
     */
    public function approveApplication(ListingApplication $application): ListingApplication
    {
        $application->listing->update(['is_active' => true]);

        $application->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return $application->refresh();
    }

    /**
     * Reject application: Delete the listing and mark as rejected
     */
    public function rejectApplication(ListingApplication $application, string $reason = null): ListingApplication
    {
        $application->listing->update(['is_active' => false]);

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return $application->refresh();
    }

    /**
     * Decode a base64 image string and save it to storage.
     * Returns the relative file path.
     */
    private function saveBase64Image(string $base64Image, int $listingId): string
    {
        // Extract mime type and base64 data
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $extension = $matches[1];
            $base64Data = substr($base64Image, strpos($base64Image, ',') + 1);
        } else {
            // Assume it's raw base64 without the data URI prefix
            $extension = 'png';
            $base64Data = $base64Image;
        }

        $imageData = base64_decode($base64Data);

        if ($imageData === false) {
            throw new \InvalidArgumentException('Invalid base64 image data');
        }

        $fileName = Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($fileName, $imageData);

        return $fileName;
    }
}
