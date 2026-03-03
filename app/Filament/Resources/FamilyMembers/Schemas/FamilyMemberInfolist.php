<?php

namespace App\Filament\Resources\FamilyMembers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FamilyMemberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label(__('User')),
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('national_id')
                    ->label(__('National ID')),
                TextEntry::make('relation')
                    ->label(__('Relation'))
                    ->badge(),
                TextEntry::make('birth_date')
                    ->label(__('Birth Date'))
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->label(__('Gender'))
                    ->badge(),
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
