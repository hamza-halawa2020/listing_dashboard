<?php

namespace App\Filament\Resources\ListingResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;

class PhonesRelationManager extends RelationManager
{
    protected static string $relationship = 'phones';

    protected static ?string $title = 'Phones';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->required()
                    ->tel()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Type ')
                    ->options([
                        'landline' => 'Landline',
                        'mobile' => 'Mobile',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->required()
                    ->default('mobile'),
                TextInput::make('contact_person')
                    ->label('Contact Person')
                    ->maxLength(255)
                    ->placeholder('Optional - e.g., Dr. Ahmed, Reception'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone_number')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('contact_person')
                    ->label('Contact Person')
                    ->searchable()
                    ->default('—'),
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
