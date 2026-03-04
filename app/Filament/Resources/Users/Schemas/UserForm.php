<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\FamilyMember;
use App\Models\User;
use App\Support\AdminPermissionRegistry;
use App\Support\FamilyMemberSubscription;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Livewire\Component as Livewire;

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
                Select::make('roles')
                    ->label(__('Roles'))
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => AdminPermissionRegistry::roleLabel($record->name))
                    ->multiple()
                    ->preload()
                    ->searchable(),
                TextInput::make('national_id')
                    ->label(__('National ID')),
                Select::make('location_id')
                    ->label(__('Location'))
                    ->relationship('location', 'name', fn ($query) => $query->orderedForDisplay())
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
                    ->hiddenOn('create')
                    ->schema([
                        Select::make('subscription_id')
                            ->label(__('Subscription'))
                            ->options(function (Livewire $livewire, $record): array {
                                $user = $livewire->record ?? null;
                                $familyMember = $record instanceof FamilyMember ? $record : null;

                                return $user instanceof User
                                    ? FamilyMemberSubscription::optionsForUser($user, $familyMember)
                                    : [];
                            })
                            ->placeholder(__('Not assigned'))
                            ->searchable()
                            ->preload(),
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
                    ->addActionLabel(__('Add Family Member'))
                    ->helperText(__('You can add all family members. Only the linked members count against the subscription limit.')),
            ]);
    }
}
