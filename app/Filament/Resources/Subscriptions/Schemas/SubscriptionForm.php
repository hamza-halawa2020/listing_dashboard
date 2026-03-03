<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('subscription_plan_id')
                    ->relationship('subscriptionPlan', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('starts_at')
                    ->required(),
                DatePicker::make('ends_at')
                    ->required(),
                Select::make('status')
                    ->options(['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled'])
                    ->default('active')
                    ->required(),
                TextInput::make('payment_reference'),
                Select::make('payment_method')
                    ->options([
            'cash' => 'Cash',
            'credit_card' => 'Credit card',
            'bank_transfer' => 'Bank transfer',
            'fawry' => 'Fawry',
            'instapay' => 'Instapay',
            'vodafone_cash' => 'Vodafone cash',
        ]),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
