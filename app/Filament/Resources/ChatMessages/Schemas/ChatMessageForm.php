<?php

namespace App\Filament\Resources\ChatMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Schema;

class ChatMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('chat_conversation_id')
                    ->label(__('Conversation'))
                    ->relationship('conversation', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('sender_id')
                    ->label(__('Sender'))
                    ->relationship('sender', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('body')
                    ->label(__('Message'))
                    ->required()
                    ->columnSpanFull(),
                KeyValue::make('meta')
                    ->label(__('Meta'))
                    ->columnSpanFull(),
            ]);
    }
}
