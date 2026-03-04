<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('attachment')
                    ->label(__('Image'))
                    ->disk('public')
                    ->square(),
                TextEntry::make('user.name')
                    ->label(__('User')),
                TextEntry::make('subscription.id')
                    ->label(__('Subscription #'))
                    ->placeholder('-'),
                TextEntry::make('subscription.membership_card_number')
                    ->label(__('Membership Number'))
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->label(__('Amount'))
                    ->money('egp'),
                TextEntry::make('payment_method')
                    ->label(__('Payment Method'))
                    ->badge(),
                TextEntry::make('transaction_reference')
                    ->label(__('Transaction Reference'))
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextEntry::make('notes')
                    ->label(__('Notes'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('paid_at')
                    ->label(__('Paid At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
