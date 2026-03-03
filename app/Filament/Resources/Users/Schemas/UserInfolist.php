<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('email')
                    ->label(__('Email address')),
                TextEntry::make('phone')
                    ->label(__('Phone'))
                    ->placeholder('-'),
                TextEntry::make('role')
                    ->label(__('Role'))
                    ->badge(),
                TextEntry::make('national_id')
                    ->label(__('National ID'))
                    ->placeholder('-'),
                TextEntry::make('location.name')
                    ->label(__('Location'))
                    ->placeholder('-'),
                TextEntry::make('birth_date')
                    ->label(__('Birth Date'))
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->label(__('Gender'))
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->label(__('Address'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('email_verified_at')
                    ->label(__('Email Verified At'))
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
