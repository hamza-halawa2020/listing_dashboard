<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingPhone;
use App\Models\ListingWorkingHour;
use App\Models\Location;
use App\Models\Offer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ListingSeeder extends Seeder
{
    /**
     * Get coordinates from location name using Nominatim API
     */
    private function getCoordinatesFromLocation($locationName): ?array
    {
        try {
            // Build search query with just location and Egypt
            $searchQuery = "$locationName, Cairo, Egypt";
            
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'ListingApp/1.0'
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $searchQuery,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'eg',
                ]);
            
            if ($response->successful() && count($response->json()) > 0) {
                $result = $response->json()[0];
                return [
                    'latitude' => (float) $result['lat'],
                    'longitude' => (float) $result['lon'],
                ];
            }
        } catch (\Exception $e) {
            // Silently fail and return null
        }
        
        return null;
    }

    public function run(): void
    {
        $created = 0;
        $skipped = 0;
        
        // Read CSV file
        $csvPath = base_path('جدول بيانات بدون عنوان - الورقة1 (1).csv');
        
        if (!File::exists($csvPath)) {
            echo "CSV file not found at: $csvPath\n";
            return;
        }
        
        $file = fopen($csvPath, 'r');
        
        // Skip header row
        $header = fgetcsv($file);
        
        // Process each row
        while (($row = fgetcsv($file)) !== false) {
            // Skip empty rows
            if (empty($row[1])) {
                continue;
            }
            
            $index = $row[0] ?? '';
            $name = $row[1] ?? '';
            $categoryName = $row[2] ?? '';
            $locationName = $row[3] ?? '';
            $address = $row[4] ?? '';
            $phone = $row[5] ?? '';
            $offerText = $row[6] ?? '';
            $contactPerson = $row[7] ?? '';
            $contactPersonPhone = $row[8] ?? '';
            $notes = $row[9] ?? '';
            
            // Clean up data
            $name = trim($name);
            $categoryName = trim($categoryName);
            
            // Remove newlines from category name
            $categoryName = str_replace(["\n", "\r"], ' ', $categoryName);
            $categoryName = preg_replace('/\s+/', ' ', $categoryName); // Replace multiple spaces with single space
            
            // Fix common category name issues
            $categoryName = str_replace('مخ واعصابنفسية وعصبية', 'مخ واعصاب', $categoryName);
            $categoryName = str_replace('مخ واعصاب نفسية وعصبية', 'مخ واعصاب', $categoryName);
            $categoryName = str_replace('ساء وتوليد', 'نساء وتوليد', $categoryName);
            
            $locationName = trim($locationName);
            
            // Fix common location name issues
            $locationName = str_replace('حدائق لمعادى', 'حدائق المعادى', $locationName);
            $locationName = str_replace('مدية نصر', 'مدينة نصر', $locationName);
            
            $address = trim($address);
            $phone = trim(str_replace([' ', "\n", "\r"], '', $phone));
            $offerText = trim($offerText);
            $contactPerson = trim(str_replace(["\n", "\r"], ' ', $contactPerson));
            $contactPersonPhone = trim(str_replace([' ', "\n", "\r"], '', $contactPersonPhone));
            
            // Validate required fields
            if (empty($name)) {
                $skipped++;
                echo "Skipped row $index: No name\n";
                continue;
            }
            
            // Get category
            if (empty($categoryName)) {
                $skipped++;
                echo "Skipped $name: No category name\n";
                continue;
            }
            
            $category = Category::where('name', $categoryName)->first();
            if (!$category) {
                $skipped++;
                echo "Skipped $name: Category '$categoryName' not found\n";
                continue;
            }

            // Get location
            if (empty($locationName)) {
                $skipped++;
                echo "Skipped $name: No location name\n";
                continue;
            }
            
            $location = Location::where('name', $locationName)->first();
            if (!$location) {
                $skipped++;
                echo "Skipped $name: Location '$locationName' not found\n";
                continue;
            }

            // Get coordinates from location (cache to avoid repeated API calls)
            static $locationCoordinatesCache = [];
            
            $coordinates = null;
            if (!empty($locationName)) {
                // Check cache first
                if (isset($locationCoordinatesCache[$locationName])) {
                    $coordinates = $locationCoordinatesCache[$locationName];
                    if ($coordinates) {
                        echo "Geocoding: $name... ✓ (cached: {$coordinates['latitude']}, {$coordinates['longitude']})\n";
                    }
                } else {
                    echo "Geocoding: $name ($locationName)... ";
                    $coordinates = $this->getCoordinatesFromLocation($locationName);
                    
                    // Cache the result (even if null)
                    $locationCoordinatesCache[$locationName] = $coordinates;
                    
                    if ($coordinates) {
                        echo "✓ ({$coordinates['latitude']}, {$coordinates['longitude']})\n";
                    } else {
                        echo "✗ (no coordinates found)\n";
                    }
                    
                    // Sleep to respect API rate limits (1 request per second)
                    sleep(1);
                }
            }

            // Create listing
            $listing = Listing::create([
                'name' => $name,
                'category_id' => $category->id,
                'location_id' => $location->id,
                'address' => $address,
                'description' => 'خدمات طبية متميزة',
                'is_active' => true,
                'latitude' => $coordinates['latitude'] ?? null,
                'longitude' => $coordinates['longitude'] ?? null,
            ]);

            // Add main phone if exists
            if (!empty($phone)) {
                // Split multiple phones if they exist (separated by newline in CSV)
                $phones = array_filter(array_map('trim', explode("\n", $phone)));
                
                foreach ($phones as $phoneNumber) {
                    if (!empty($phoneNumber)) {
                        ListingPhone::create([
                            'listing_id' => $listing->id,
                            'phone_number' => $phoneNumber,
                            'type' => 'mobile',
                            'contact_person' => !empty($contactPerson) ? $contactPerson : null,
                        ]);
                    }
                }
            }
            
            // Add contact person phone if different from main phone
            if (!empty($contactPersonPhone) && $contactPersonPhone != $phone) {
                $contactPhones = array_filter(array_map('trim', explode("\n", $contactPersonPhone)));
                
                foreach ($contactPhones as $phoneNumber) {
                    if (!empty($phoneNumber) && !in_array($phoneNumber, explode("\n", $phone))) {
                        ListingPhone::create([
                            'listing_id' => $listing->id,
                            'phone_number' => $phoneNumber,
                            'type' => 'mobile',
                            'contact_person' => !empty($contactPerson) ? $contactPerson : null,
                        ]);
                    }
                }
            }

            // Add offer if exists
            if (!empty($offerText) && $offerText != 'مفيش عرض') {
                // Extract discount percentage (first number found)
                preg_match('/(\d+)\s*%/', $offerText, $matches);
                $discountPercentage = isset($matches[1]) ? (int)$matches[1] : 0;

                Offer::create([
                    'listing_id' => $listing->id,
                    'title' => 'عرض خاص',
                    'description' => $offerText,
                    'discount_percentage' => $discountPercentage,
                    'is_active' => true,
                ]);
            }
            
            // Add default working hours (Saturday to Thursday: 9 AM - 5 PM, Friday: Closed)
            $workingDays = [
                ['day' => 'saturday', 'day_ar' => 'السبت', 'is_closed' => false],
                ['day' => 'sunday', 'day_ar' => 'الأحد', 'is_closed' => false],
                ['day' => 'monday', 'day_ar' => 'الإثنين', 'is_closed' => false],
                ['day' => 'tuesday', 'day_ar' => 'الثلاثاء', 'is_closed' => false],
                ['day' => 'wednesday', 'day_ar' => 'الأربعاء', 'is_closed' => false],
                ['day' => 'thursday', 'day_ar' => 'الخميس', 'is_closed' => false],
                ['day' => 'friday', 'day_ar' => 'الجمعة', 'is_closed' => true],
            ];
            
            foreach ($workingDays as $dayInfo) {
                ListingWorkingHour::create([
                    'listing_id' => $listing->id,
                    'day' => $dayInfo['day'],
                    'open_time' => $dayInfo['is_closed'] ? null : '09:00',
                    'close_time' => $dayInfo['is_closed'] ? null : '17:00',
                    'is_closed' => $dayInfo['is_closed'],
                ]);
            }
            
            $created++;
        }
        
        fclose($file);
        
        echo "\n=== Summary ===\n";
        echo "Created: $created\n";
        echo "Skipped: $skipped\n";
        echo "Total: " . ($created + $skipped) . "\n";
    }
}
