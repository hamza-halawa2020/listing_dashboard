<?php

namespace App\Filament\Resources\Locations;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\Locations\Pages\ManageLocations;
use App\Filament\Resources\Locations\Schemas\LocationForm;
use App\Filament\Resources\Locations\Tables\LocationsTable;
use App\Models\Location;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LocationResource extends AuthorizedResource
{
    protected static ?string $model = Location::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('Location');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Locations');
    }

    public static function form(Schema $schema): Schema
    {
        return LocationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        // Parents first, then children grouped under their parent, all ordered by priority name
        return parent::getEloquentQuery()
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw('COALESCE(parent_id, id)')
            ->orderedForDisplay();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLocations::route('/'),
        ];
    }
}
