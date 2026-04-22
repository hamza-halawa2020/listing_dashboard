<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use App\Models\Referral;
use App\Models\PointSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('code')
                    ->label(__('Code'))
                    ->required(),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'individual' => __('Individual'),
                        'family' => __('Family'),
                    ])
                    ->required(),
                Select::make('coverage_type')
                    ->label(__('Coverage Type'))
                    ->options([
                        'zone' => __('Zone'),
                        'governorate' => __('Governorate'),
                        'national' => __('National'),
                    ])
                    ->required(),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->required()
                    ->numeric()
                    ->prefix(__('EGP')),
                Placeholder::make('points_price_calculated')
                    ->label(__('Points Required'))
                    ->content(function ($get) {
                        $price = (float) $get('price');
                        $pointsNeeded = PointSetting::calculatePointsNeeded($price);
                        $currentRate = PointSetting::getCurrentRate();
                        
                        return " {$pointsNeeded} points"
                            . " ({$currentRate} EGP/point = " . number_format($price, 2) . " EGP)";
                    })
                    ->helperText(__('Points calculated automatically based on price and current point rate.')),
                TextInput::make('duration_days')
                    ->label(__('Duration Days'))
                    ->required()
                    ->numeric()
                    ->default(365),
                TextInput::make('max_family_members')
                    ->label(__('Max Family Members'))
                    ->numeric(),
                TextInput::make('subscription_reward_points')
                    ->label('Subscription Reward Points')
                    ->helperText('Points awarded when this specific plan is approved for the user.')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                Section::make(__('Referral Rewards'))
                    ->description(__('Points and rewards given when a referred user subscribes to this plan.'))
                    ->schema([
                        TextInput::make('referrer_reward_points')
                            ->label(__('Referrer reward points'))
                            ->helperText(__('Points awarded to the user who shared the referral code.'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                        Select::make('referee_reward_type')
                            ->label(__('New user reward type'))
                            ->options([
                                Referral::REWARD_NONE => __('None'),
                                Referral::REWARD_POINTS => __('Points'),
                                Referral::REWARD_FIXED_DISCOUNT => __('Fixed discount'),
                                Referral::REWARD_PERCENT_DISCOUNT => __('Percent discount'),
                            ])
                            ->default(Referral::REWARD_NONE)
                            ->native(false)
                            ->required(),
                        TextInput::make('referee_reward_value')
                            ->label(__('New user reward value'))
                            ->helperText(__('Points number, fixed amount, or percent (0-100) depending on reward type.'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                    ])->columns(3),
            ]);
    }
}
