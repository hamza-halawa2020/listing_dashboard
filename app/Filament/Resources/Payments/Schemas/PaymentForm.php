<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
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
                Select::make('subscription_id')
                    ->label(__('Subscription'))
                    ->relationship('subscription', 'id')
                    ->searchable(),
                TextInput::make('amount')
                    ->label(__('Amount'))
                    ->required()
                    ->numeric()
                    ->prefix(__('EGP')),
                Select::make('payment_method')
                    ->label(__('Payment Method'))
                    ->options([
                        'cash' => __('Cash'),
                        'credit_card' => __('Credit card'),
                        'bank_transfer' => __('Bank transfer'),
                        'fawry' => __('Fawry'),
                        'instapay' => __('Instapay'),
                        'vodafone_cash' => __('Vodafone cash'),
                    ])
                    ->required(),
                TextInput::make('transaction_reference')
                    ->label(__('Transaction Reference')),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'completed' => __('Completed'),
                        'failed' => __('Failed'),
                        'refunded' => __('Refunded'),
                    ])
                    ->default('pending')
                    ->required(),
                FileUpload::make('attachment')
                    ->label(__('Image'))
                    ->image(),
                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
                Select::make('location_id')
                    ->label(__('Location'))
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('delivery_required')
                    ->label(__('Delivery Required'))
                    ->options([
                        0 => __('No'),
                        1 => __('Yes'),
                    ])
                    ->default(0)
                    ->required(),
                TextInput::make('delivery_name')
                    ->label(__('Delivery Name')),
                TextInput::make('delivery_phone')
                    ->label(__('Delivery Phone')),
                Textarea::make('delivery_address')
                    ->label(__('Delivery Address'))
                    ->columnSpanFull(),
                TextInput::make('shipping_cost')
                    ->label(__('Shipping Cost'))
                    ->numeric()
                    ->prefix(__('EGP')),
                DateTimePicker::make('paid_at')
                    ->label(__('Paid At')),
            ]);
    }
}
