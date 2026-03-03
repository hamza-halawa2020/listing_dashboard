<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\ManageReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?string $recordTitleAttribute = 'review';

    public static function getModelLabel(): string
    {
        return __('Review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Reviews');
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('review')
                    ->label(__('Review'))
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('status')
                    ->label(__('Status'))
                    ->required(),
                Hidden::make('approved_by')
                    ->label(__('Approved By'))
                    ->dehydrated()
                    ->dehydrateStateUsing(fn ($state, $get) => $get('status') ? auth()->id() : null),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('createdBy.name')
                    ->label(__('Name')),
                TextEntry::make('review')
                    ->label(__('Review'))
                    ->columnSpanFull(),
                IconEntry::make('status')
                    ->label(__('Status'))
                    ->boolean(),
                TextEntry::make('user.name')
                    ->label(__('User')),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('createdBy.name')
            ->columns([
                TextColumn::make('createdBy.name')
                    ->label(__('Name'))
                    ->searchable(),
                IconColumn::make('status')
                    ->label(__('Status'))
                    ->boolean(),
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            // 'index' => ManageReviews::route('/'),
        ];
    }
}
