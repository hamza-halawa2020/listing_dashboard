<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('User'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('subscription_plan_id')
                    ->label(__('Subscription Plan'))
                    ->relationship('subscriptionPlan', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('membership_card_number')
                    ->label(__('Membership Number'))
                    ->disabled()
                    ->dehydrated(false),
                Toggle::make('is_card_issued')
                    ->label(__('Card Issued'))
                    ->inline(false),
                DateTimePicker::make('card_issued_at')
                    ->label(__('Card Issued At'))
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (callable $get): bool => (bool) $get('is_card_issued') || filled($get('card_issued_at')))
                    ->seconds(false),
                DatePicker::make('starts_at')
                    ->label(__('Starts At'))
                    ->required(),
                DatePicker::make('ends_at')
                    ->label(__('Ends At'))
                    ->required(),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'active' => __('Active'),
                        'expired' => __('Expired'),
                        'cancelled' => __('Cancelled'),
                    ])
                    ->default('active')
                    ->required(),
                TextInput::make('payment_reference')
                    ->label(__('Payment Reference')),
                Select::make('payment_method')
                    ->label(__('Payment Method'))
                    ->options([
                        'cash' => __('Cash'),
                        'credit_card' => __('Credit card'),
                        'bank_transfer' => __('Bank transfer'),
                        'fawry' => __('Fawry'),
                        'instapay' => __('Instapay'),
                        'vodafone_cash' => __('Vodafone cash'),
                    ]),
                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
            ]);
    }
}
