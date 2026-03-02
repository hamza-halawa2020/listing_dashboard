<?php

namespace App\Filament\Resources\Locations\Schemas;

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
                    ->required()
                    ->maxLength(255),
                    \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'governorate' => 'Governorate',
                        'zone' => 'Zone/Area',
                    ])
                    ->required()
                    ->default('zone')
                    ->reactive()
                    ->label('Type'),
                \Filament\Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name', fn ($query) => $query->where('type', 'governorate'))
                    ->searchable()
                    ->preload()
                    ->label('Parent Location (e.g., Country or City)')
                    ->visible(fn (Get $get) => $get('type') === 'zone')
                    ->required(fn (Get $get) => $get('type') === 'zone'),
                
                TextInput::make('shipping_cost')
                    ->numeric()
                    ->label('Shipping Cost')
                    ->helperText('Only needed for governorate records; e.g. 90')
                    ->visible(fn (Get $get) => $get('type') === 'governorate'),
            ]);
    }
}
