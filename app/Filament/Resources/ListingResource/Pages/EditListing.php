<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use App\Models\Location;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListing extends EditRecord
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => ListingResource::canDelete($this->getRecord())),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get the location hierarchy
        if (isset($data['location_id'])) {
            $locationId = $data['location_id'];
            $hierarchy = $this->getLocationHierarchy($locationId);
            
            // Fill the location levels
            if (count($hierarchy) > 0) $data['location_level_1'] = $hierarchy[0] ?? null;
            if (count($hierarchy) > 1) $data['location_level_2'] = $hierarchy[1] ?? null;
            if (count($hierarchy) > 2) $data['location_level_3'] = $hierarchy[2] ?? null;
            if (count($hierarchy) > 3) $data['location_level_4'] = $hierarchy[3] ?? null;
            if (count($hierarchy) > 4) $data['location_level_5'] = $hierarchy[4] ?? null;
            
            // Keep the original location_id (don't let it become 0)
            // The afterStateHydrated will handle setting it correctly
        }

        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getLocationHierarchy($locationId): array
    {
        $hierarchy = [];
        $currentLocation = Location::find($locationId);
        
        while ($currentLocation) {
            array_unshift($hierarchy, $currentLocation->id);
            $currentLocation = $currentLocation->parent;
        }
        
        return $hierarchy;
    }
}
