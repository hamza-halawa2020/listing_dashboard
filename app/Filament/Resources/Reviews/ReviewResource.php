<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\Reviews\Pages\ManageReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends AuthorizedResource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;
    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'review';

    public static function getModelLabel(): string
    {
        return __('Comment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Comments');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['createdBy', 'post', 'approvedBy']);
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
                TextEntry::make('post.title')
                    ->label(__('Post'))
                    ->placeholder('-'),
                TextEntry::make('author_name')
                    ->label(__('Name'))
                    ->placeholder('-'),
                TextEntry::make('author_phone')
                    ->label(__('Phone'))
                    ->placeholder('-'),
                TextEntry::make('review')
                    ->label(__('Comment'))
                    ->columnSpanFull(),
                IconEntry::make('status')
                    ->label(__('Approved'))
                    ->boolean(),
                TextEntry::make('approvedBy.name')
                    ->label(__('Approved By'))
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('author_name')
            ->columns([
                TextColumn::make('post.title')
                    ->label(__('Post'))
                    ->searchable(),
                TextColumn::make('author_name')
                    ->label(__('Name'))
                    ->state(fn (Review $record): ?string => $record->author_name)
                    ->placeholder('-'),
                TextColumn::make('author_phone')
                    ->label(__('Phone'))
                    ->state(fn (Review $record): ?string => $record->author_phone)
                    ->placeholder('-'),
                TextColumn::make('review')
                    ->label(__('Comment'))
                    ->limit(50)
                    ->tooltip(fn (Review $record): string => $record->review),
                ToggleColumn::make('status')
                    ->label(__('Approved'))
                    ->sortable()
                    ->afterStateUpdated(function (Review $record, bool $state): void {
                        $record->approved_by = $state ? auth()->id() : null;
                        $record->save();
                    }),
                TextColumn::make('approvedBy.name')
                    ->label(__('Approved By'))
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn ($record): bool => static::canView($record)),
                EditAction::make()
                    ->visible(fn ($record): bool => static::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn ($record): bool => static::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canDeleteAny()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReviews::route('/'),
        ];
    }
}
