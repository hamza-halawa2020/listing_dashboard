<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use App\Models\Location;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLocations extends ManageRecords
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => LocationResource::canCreate())
                ->before(function (CreateAction $action, array $data) {
                    // If the user already confirmed they want to add despite duplicate, skip the check
                    $confirmedName = session()->pull('location_duplicate_confirmed');
                    if ($confirmedName === trim($data['name'])) {
                        return;
                    }

                    $existing = Location::query()
                        ->where('name', trim($data['name']))
                        ->first();

                    if (! $existing) {
                        return;
                    }

                    // Store the name so the next submit (after confirmation) skips the check
                    session()->put('location_duplicate_confirmed', trim($data['name']));

                    $action
                        ->requiresConfirmation()
                        ->modalHeading(__('Location Already Exists'))
                        ->modalDescription(
                            __('A location with the name ":name" already exists. Do you want to add it again anyway?', [
                                'name' => $existing->name,
                            ])
                        )
                        ->modalSubmitActionLabel(__('Add Anyway'))
                        ->modalCancelActionLabel(__('Cancel'));

                    $action->halt();
                }),
        ];
    }
}
