<?php

namespace App\Filament\Resources\FamilyMembers\Schemas;

use App\Models\FamilyMember;
use App\Models\User;
use App\Support\FamilyMemberSubscription;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FamilyMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('User'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('subscription_id', null))
                    ->required(),
                Select::make('subscription_id')
                    ->label(__('Subscription'))
                    ->options(function (Get $get, $record): array {
                        $user = User::find($get('user_id'));
                        $familyMember = $record instanceof FamilyMember ? $record : null;

                        return FamilyMemberSubscription::optionsForUser($user, $familyMember);
                    })
                    ->placeholder(__('Not assigned'))
                    ->searchable()
                    ->preload()
                    ->helperText(__('Optional. Only linked family members count toward the subscription limit.')),
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
            ]);
    }
}
