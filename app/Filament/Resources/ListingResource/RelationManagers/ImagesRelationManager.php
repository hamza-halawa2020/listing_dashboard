<?php

namespace App\Filament\Resources\ListingResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Images');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image_path')
                    ->label(__('Image'))
                    ->image()
                    ->required(),
                Toggle::make('is_cover')
                    ->label(__('Cover Image'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label(__('Image'))
                    ->square(),
                IconColumn::make('is_cover')
                    ->label(__('Cover'))
                    ->boolean(),
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
