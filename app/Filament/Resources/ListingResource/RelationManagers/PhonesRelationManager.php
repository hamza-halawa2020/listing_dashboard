<?php

namespace App\Filament\Resources\ListingResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PhonesRelationManager extends RelationManager
{
    protected static string $relationship = 'phones';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Phones');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone_number')
                    ->label(__('Phone Number'))
                    ->required()
                    ->tel()
                    ->maxLength(255),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'landline' => __('Landline'),
                        'mobile' => __('Mobile'),
                        'whatsapp' => __('WhatsApp'),
                    ])
                    ->required()
                    ->default('mobile'),
                TextInput::make('contact_person')
                    ->label(__('Contact Person'))
                    ->maxLength(255)
                    ->placeholder(__('Optional - e.g., Dr. Ahmed, Reception')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone_number')
                    ->label(__('Phone Number'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge(),
                TextColumn::make('contact_person')
                    ->label(__('Contact Person'))
                    ->searchable()
                    ->default('-'),
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
