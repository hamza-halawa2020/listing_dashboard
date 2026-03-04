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
                    ->disabledOn('edit')
                    ->required(),
                Select::make('subscription_id')
                    ->label(__('Subscription'))
                    ->relationship('subscription', 'id')
                    ->disabledOn('edit')
                    ->searchable(),
                TextInput::make('amount')
                    ->label(__('Amount'))
                    ->required()
                    ->numeric()
                    ->disabledOn('edit')
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
                    ->disabledOn('edit')
                    ->required(),
                TextInput::make('transaction_reference')
                    ->label(__('Transaction Reference'))
                    ->disabledOn('edit'),
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
                    ->disabledOn('edit')
                    ->disk('public')
                    ->directory('payments')
                    ->visibility('public')
                    ->image(),
                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->disabledOn('edit')
                    ->columnSpanFull(),
                Select::make('location_id')
                    ->label(__('Location'))
                    ->relationship('location', 'name', fn ($query) => $query->orderedForDisplay())
                    ->searchable()
                    ->disabledOn('edit')
                    ->preload(),
                Select::make('delivery_required')
                    ->label(__('Delivery Required'))
                    ->options([
                        0 => __('No'),
                        1 => __('Yes'),
                    ])
                    ->disabledOn('edit')
                    ->default(0),
                TextInput::make('delivery_name')
                    ->label(__('Delivery Name'))
                    ->disabledOn('edit'),
                TextInput::make('delivery_phone')
                    ->label(__('Delivery Phone'))
                    ->disabledOn('edit'),
                Textarea::make('delivery_address')
                    ->label(__('Delivery Address'))
                    ->disabledOn('edit')
                    ->columnSpanFull(),
                TextInput::make('shipping_cost')
                    ->label(__('Shipping Cost'))
                    ->numeric()
                    ->disabledOn('edit')
                    ->prefix(__('EGP')),
                DateTimePicker::make('paid_at')
                    ->label(__('Paid At'))
                    ->disabledOn('edit'),
            ]);
    }
}
