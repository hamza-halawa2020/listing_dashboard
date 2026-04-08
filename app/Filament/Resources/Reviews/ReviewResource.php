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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends AuthorizedResource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;
    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'Review';

    public static function getModelLabel(): string
    {
        return __('Review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Reviews');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['createdBy', 'approvedBy']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('review')
                    ->label(__('Review'))
                    ->required()
                    ->columnSpanFull(),
                Select::make('rating')
                    ->label(__('Rating'))
                    ->options([
                        1 => '⭐ 1',
                        2 => '⭐⭐ 2',
                        3 => '⭐⭐⭐ 3',
                        4 => '⭐⭐⭐⭐ 4',
                        5 => '⭐⭐⭐⭐⭐ 5',
                    ])
                    ->required(),
                TextInput::make('guest_name')
                    ->label(__('Name'))
                    ->nullable(),
                TextInput::make('guest_phone')
                    ->label(__('Phone'))
                    ->tel()
                    ->nullable(),
                TextInput::make('guest_email')
                    ->label(__('Email'))
                    ->email()
                    ->nullable(),
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
                TextEntry::make('author_name')
                    ->label(__('Name'))
                    ->placeholder('-'),
                TextEntry::make('author_phone')
                    ->label(__('Phone'))
                    ->placeholder('-'),
                TextEntry::make('author_email')
                    ->label(__('Email'))
                    ->placeholder('-'),
                TextEntry::make('rating')
                    ->label(__('Rating'))
                    ->state(fn (Review $record): string => str_repeat('⭐', $record->rating)),
                TextEntry::make('review')
                    ->label(__('Review'))
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
                TextColumn::make('author_name')
                    ->label(__('Name'))
                    ->state(fn (Review $record): ?string => $record->author_name)
                    ->placeholder('-'),
                TextColumn::make('author_phone')
                    ->label(__('Phone'))
                    ->state(fn (Review $record): ?string => $record->author_phone)
                    ->placeholder('-'),
                TextColumn::make('rating')
                    ->label(__('Rating'))
                    ->state(fn (Review $record): string => str_repeat('⭐', $record->rating))
                    ->sortable(),
                TextColumn::make('review')
                    ->label(__('Review'))
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
                Filter::make('approved')
                    ->query(fn (Builder $query): Builder => $query->where('status', true))
                    ->toggle(),
                Filter::make('pending')
                    ->query(fn (Builder $query): Builder => $query->where('status', false))
                    ->toggle(),
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
