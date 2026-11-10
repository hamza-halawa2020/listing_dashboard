<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Exports\Exporters\CategoryExporter;
use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use App\Support\CategoryIcons;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Tables\Filters\Filter;

class CategoryResource extends AuthorizedResource
{
    protected static ?string $model = Category::class;
    protected static ?int $navigationSort = 2;


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('Category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Categories');
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('parent_id')
                    ->label(__('Parent Category'))
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->live()
                    ->helperText(fn ($state): string => blank($state)
                        ? __('This is a parent category. Set a color and icon to display on the website.')
                        : __('Color and icon are inherited from the parent category.')),
                ColorPicker::make('color')
                    ->label(__('Color'))
                    ->visible(fn (callable $get): bool => blank($get('parent_id')))
                    ->helperText(__('Shown on the website for this category and its sub-categories.')),
                Select::make('icon')
                    ->label(__('Icon'))
                    ->visible(fn (callable $get): bool => blank($get('parent_id')))
                    ->allowHtml()
                    ->searchable()
                    ->options(CategoryIcons::options()),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('slug')
                    ->label(__('Slug')),
                TextEntry::make('parent.name')
                    ->label(__('Parent Category'))
                    ->placeholder('-'),
                TextEntry::make('color')
                    ->label(__('Color'))
                    ->placeholder('-')
                    ->visible(fn ($record): bool => blank($record->parent_id)),
                TextEntry::make('icon')
                    ->label(__('Icon'))
                    ->placeholder('-')
                    ->visible(fn ($record): bool => blank($record->parent_id)),
                TextEntry::make('createdBy.name')
                    ->label(__('Created By'))
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
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('Color'))
                    ->toggleable(),
                TextColumn::make('icon')
                    ->label(__('Icon'))
                    ->toggleable()
                    ->formatStateUsing(fn (?string $state): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString(
                        $state
                            ? \Illuminate\Support\Facades\Blade::render("@svg('{$state}', 'icon-svg', ['style' => 'width:1rem;height:1rem;display:inline-block'])")
                            : '-'
                    )),
                TextColumn::make('parent.name')
                    ->label(__('Parent'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('createdBy.name')
                    ->label(__('Created By'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('parent_only')
                    ->label(__('Parent Categories Only'))
                    ->query(fn ($query) => $query->whereNull('parent_id')),
            ])
            ->headerActions([
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
                ExportAction::make()->exporter(CategoryExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canDeleteAny()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
