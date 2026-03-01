<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                Select::make('type')
                    ->options(['individual' => 'Individual', 'family' => 'Family'])
                    ->required(),
                Select::make('coverage_type')
                    ->options(['zone' => 'Zone', 'governorate' => 'Governorate', 'national' => 'National'])
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('duration_days')
                    ->required()
                    ->numeric()
                    ->default(365),
                TextInput::make('max_family_members')
                    ->numeric(),
            ]);
    }
}
