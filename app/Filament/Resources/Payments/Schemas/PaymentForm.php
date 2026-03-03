<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class PaymentForm
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
                Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->searchable(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('payment_method')
                    ->options([
            'cash' => 'Cash',
            'credit_card' => 'Credit card',
            'bank_transfer' => 'Bank transfer',
            'fawry' => 'Fawry',
            'instapay' => 'Instapay',
            'vodafone_cash' => 'Vodafone cash',
        ])
                    ->required(),
                TextInput::make('transaction_reference'),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ])
                    ->default('pending')
                    ->required(),
                FileUpload::make('attachment')
                    ->image()
                    // ->directory('payments/attachments')
                    ,
                Textarea::make('notes')
                    ->columnSpanFull(),
                Select::make('location_id')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('delivery_required')
                    ->options([
                        0 => 'No',
                        1 => 'Yes',
                    ])
                    ->default(0)
                    ->required(),
                TextInput::make('delivery_name'),
                TextInput::make('delivery_phone'),
                Textarea::make('delivery_address')
                    ->columnSpanFull(),
                TextInput::make('shipping_cost')
                    ->numeric(),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
