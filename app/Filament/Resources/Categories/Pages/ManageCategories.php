<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => CategoryResource::canCreate())
                ->mutateFormDataUsing(function (array $data) {
                    $data['created_by'] = auth()->id();
                    return $data;
                })
                ->before(function (CreateAction $action, array $data) {
                    // If the user already confirmed they want to add despite duplicate, skip the check
                    $confirmedName = session()->pull('category_duplicate_confirmed');
                    if ($confirmedName === trim($data['name'])) {
                        return;
                    }

                    $existing = Category::query()
                        ->where('name', trim($data['name']))
                        ->first();

                    if (! $existing) {
                        return;
                    }

                    // Store the name so the next submit (after confirmation) skips the check
                    session()->put('category_duplicate_confirmed', trim($data['name']));

                    $action
                        ->requiresConfirmation()
                        ->modalHeading(__('Category Already Exists'))
                        ->modalDescription(
                            __('A category with the name ":name" already exists. Do you want to add it again anyway?', [
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
