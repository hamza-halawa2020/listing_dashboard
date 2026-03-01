<?php

namespace App\Filament\Resources\FamilyMembers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FamilyMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('national_id')
                    ->required(),
                Select::make('relation')
                    ->options([
            'spouse' => 'Spouse',
            'son' => 'Son',
            'daughter' => 'Daughter',
            'father' => 'Father',
            'mother' => 'Mother',
            'brother' => 'Brother',
            'sister' => 'Sister',
        ])
                    ->required(),
                DatePicker::make('birth_date'),
                Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female'])
                    ->required(),
            ]);
    }
}
