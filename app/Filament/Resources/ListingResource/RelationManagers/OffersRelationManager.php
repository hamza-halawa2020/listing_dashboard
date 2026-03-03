<?php

namespace App\Filament\Resources\ListingResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Offers');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(3),
                TextInput::make('discount_percentage')
                    ->label(__('Discount %'))
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable(),
                TextColumn::make('discount_percentage')
                    ->label(__('Discount %'))
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
