<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('Email address'))
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel(),
                Select::make('role')
                    ->label(__('Role'))
                    ->options([
                        'admin' => __('Admin'),
                        'member' => __('Member'),
                        'service_provider' => __('Service provider'),
                    ])
                    ->default('member')
                    ->required(),
                TextInput::make('national_id')
                    ->label(__('National ID')),
                Select::make('location_id')
                    ->label(__('Location'))
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                DatePicker::make('birth_date')
                    ->label(__('Birth Date')),
                Select::make('gender')
                    ->label(__('Gender'))
                    ->options([
                        'male' => __('Male'),
                        'female' => __('Female'),
                    ]),
                Textarea::make('address')
                    ->label(__('Address'))
                    ->columnSpanFull(),
                DateTimePicker::make('email_verified_at')
                    ->label(__('Email Verified At')),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state)),
                Repeater::make('familyMembers')
                    ->label(__('Family Members'))
                    ->relationship()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),
                        TextInput::make('national_id')
                            ->label(__('National ID'))
                            ->required(),
                        Select::make('relation')
                            ->label(__('Relation'))
                            ->options([
                                'spouse' => __('Spouse'),
                                'son' => __('Son'),
                                'daughter' => __('Daughter'),
                                'father' => __('Father'),
                                'mother' => __('Mother'),
                                'brother' => __('Brother'),
                                'sister' => __('Sister'),
                            ])
                            ->required(),
                        DatePicker::make('birth_date')
                            ->label(__('Birth Date')),
                        Select::make('gender')
                            ->label(__('Gender'))
                            ->options([
                                'male' => __('Male'),
                                'female' => __('Female'),
                            ])
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->defaultItems(0)
                    ->addActionLabel(__('Add Family Member')),
            ]);
    }
}
