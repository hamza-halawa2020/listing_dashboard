<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'governorate' => __('Governorate'),
                        'zone' => __('Zone/Area'),
                    ])
                    ->required()
                    ->default('zone')
                    ->reactive(),
                Select::make('parent_id')
                    ->label(__('Parent Location (e.g., Country or City)'))
                    ->relationship('parent', 'name', fn ($query) => $query->where('type', 'governorate'))
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get) => $get('type') === 'zone')
                    ->required(fn (Get $get) => $get('type') === 'zone'),
                TextInput::make('shipping_cost')
                    ->label(__('Shipping Cost'))
                    ->numeric()
                    ->prefix(__('EGP'))
                    ->helperText(__('Only needed for governorate records; e.g. 90'))
                    ->visible(fn (Get $get) => $get('type') === 'governorate'),
            ]);
    }
}
