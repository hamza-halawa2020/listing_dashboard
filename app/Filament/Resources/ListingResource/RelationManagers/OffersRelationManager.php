<?php

namespace App\Filament\Resources\ListingResource\RelationManagers;

use App\Models\Offer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
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
                TextInput::make('price_before_discount')
                    ->label(__('Price Before Discount'))
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, callable $set) => static::syncCalculatedPricing($get, $set)),
                TextInput::make('discount_percentage')
                    ->label(__('Discount %'))
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, callable $set) => static::syncCalculatedPricing($get, $set)),
                TextInput::make('discount_amount')
                    ->label(__('Discount Amount'))
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->readOnly(),
                TextInput::make('price_after_discount')
                    ->label(__('Price After Discount'))
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->readOnly(),
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
                TextColumn::make('price_before_discount')
                    ->label(__('Price Before Discount'))
                    ->money('egp')
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->label(__('Discount %'))
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('price_after_discount')
                    ->label(__('Price After Discount'))
                    ->money('egp')
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->label(__('Discount Amount'))
                    ->money('egp')
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

    protected static function syncCalculatedPricing(Get $get, callable $set): void
    {
        $pricing = Offer::calculatePricing(
            $get('price_before_discount'),
            $get('discount_percentage'),
        );

        $set('discount_amount', $pricing['discount_amount']);
        $set('price_after_discount', $pricing['price_after_discount']);
    }
}
