<?php

namespace App\Filament\Resources\ChatConversations;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\ChatConversations\Pages\CreateChatConversation;
use App\Filament\Resources\ChatConversations\Pages\EditChatConversation;
use App\Filament\Resources\ChatConversations\Pages\ListChatConversations;
use App\Filament\Resources\ChatConversations\Schemas\ChatConversationForm;
use App\Filament\Resources\ChatConversations\Tables\ChatConversationsTable;
use App\Models\ChatConversation;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatConversationResource extends AuthorizedResource
{
    protected static ?string $model = ChatConversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?int $navigationSort = 8;
    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('Chat Conversation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Chat Conversations');
    }

    public static function form(Schema $schema): Schema
    {
        return ChatConversationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatConversationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatConversations::route('/'),
            'create' => CreateChatConversation::route('/create'),
            'edit' => EditChatConversation::route('/{record}/edit'),
        ];
    }
}
