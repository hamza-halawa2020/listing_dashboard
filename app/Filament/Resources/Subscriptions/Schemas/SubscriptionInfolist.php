<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label(__('User')),
                TextEntry::make('subscriptionPlan.name')
                    ->label(__('Plan')),
                TextEntry::make('membership_card_number')
                    ->label(__('Membership Number'))
                    ->placeholder('-'),
                TextEntry::make('starts_at')
                    ->label(__('Starts At'))
                    ->date(),
                TextEntry::make('ends_at')
                    ->label(__('Ends At'))
                    ->date(),
                TextEntry::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextEntry::make('payment_reference')
                    ->label(__('Payment Reference'))
                    ->placeholder('-'),
                TextEntry::make('payment_method')
                    ->label(__('Payment Method'))
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->label(__('Notes'))
                    ->placeholder('-')
                    ->columnSpanFull(),
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
