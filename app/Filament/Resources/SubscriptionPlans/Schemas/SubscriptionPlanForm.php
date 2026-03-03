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
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('code')
                    ->label(__('Code'))
                    ->required(),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'individual' => __('Individual'),
                        'family' => __('Family'),
                    ])
                    ->required(),
                Select::make('coverage_type')
                    ->label(__('Coverage Type'))
                    ->options([
                        'zone' => __('Zone'),
                        'governorate' => __('Governorate'),
                        'national' => __('National'),
                    ])
                    ->required(),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->required()
                    ->numeric()
                    ->prefix(__('EGP')),
                TextInput::make('duration_days')
                    ->label(__('Duration Days'))
                    ->required()
                    ->numeric()
                    ->default(365),
                TextInput::make('max_family_members')
                    ->label(__('Max Family Members'))
                    ->numeric(),
            ]);
    }
}
