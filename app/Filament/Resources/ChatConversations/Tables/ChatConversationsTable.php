<?php

namespace App\Filament\Resources\ChatConversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge(),
                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('creator.name')
                    ->label(__('Created By'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('participants_count')
                    ->label(__('Participants'))
                    ->counts('participants'),
                TextColumn::make('last_message_at')
                    ->label(__('Last Message At'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
