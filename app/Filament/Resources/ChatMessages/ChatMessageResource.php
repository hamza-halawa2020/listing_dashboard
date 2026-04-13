<?php

namespace App\Filament\Resources\ChatMessages;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\ChatMessages\Pages\CreateChatMessage;
use App\Filament\Resources\ChatMessages\Pages\EditChatMessage;
use App\Filament\Resources\ChatMessages\Pages\ListChatMessages;
use App\Filament\Resources\ChatMessages\Schemas\ChatMessageForm;
use App\Filament\Resources\ChatMessages\Tables\ChatMessagesTable;
use App\Models\ChatMessage;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatMessageResource extends AuthorizedResource
{
    protected static ?string $model = ChatMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?int $navigationSort = 9;
    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('Chat Message');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Chat Messages');
    }

    public static function form(Schema $schema): Schema
    {
        return ChatMessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatMessagesTable::configure($table);
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
            'index' => ListChatMessages::route('/'),
            'create' => CreateChatMessage::route('/create'),
            'edit' => EditChatMessage::route('/{record}/edit'),
        ];
    }
}
