<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
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
            }),
        ];
    }
}
