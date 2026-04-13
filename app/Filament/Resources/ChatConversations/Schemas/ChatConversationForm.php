<?php

namespace App\Filament\Resources\ChatConversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ChatConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('created_by')
                    ->label(__('Created By'))
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'direct' => __('Direct'),
                        'group' => __('Group'),
                    ])
                    ->required(),
                TextInput::make('subject')
                    ->label(__('Subject'))
                    ->maxLength(255),
                DateTimePicker::make('last_message_at')
                    ->label(__('Last Message At'))
                    ->seconds(false),
            ]);
    }
}
