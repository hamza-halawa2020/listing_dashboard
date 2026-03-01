<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'member' => 'Member', 'service_provider' => 'Service provider'])
                    ->default('member')
                    ->required(),
                TextInput::make('national_id'),
                DatePicker::make('birth_date'),
                Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female']),
                Textarea::make('address')
                    ->columnSpanFull(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state)),
                Repeater::make('familyMembers')
                    ->relationship()
                    ->schema([
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
                    ])
                    ->columnSpanFull()
                    ->defaultItems(0)
                    ->addActionLabel('Add Family Member'),
            ]);
    }
}
