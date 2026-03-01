<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Get the last filled location level as the final location_id
        $finalLocationId = null;
        
        if (!empty($data['location_level_5'])) {
            $finalLocationId = $data['location_level_5'];
        } elseif (!empty($data['location_level_4'])) {
            $finalLocationId = $data['location_level_4'];
        } elseif (!empty($data['location_level_3'])) {
            $finalLocationId = $data['location_level_3'];
        } elseif (!empty($data['location_level_2'])) {
            $finalLocationId = $data['location_level_2'];
        } elseif (!empty($data['location_level_1'])) {
            $finalLocationId = $data['location_level_1'];
        }
        
        // Set the final location_id
        $data['location_id'] = $finalLocationId;
        
        // Remove temporary location level fields
        unset($data['location_level_1']);
        unset($data['location_level_2']);
        unset($data['location_level_3']);
        unset($data['location_level_4']);
        unset($data['location_level_5']);
        
        return $data;
    }
}
