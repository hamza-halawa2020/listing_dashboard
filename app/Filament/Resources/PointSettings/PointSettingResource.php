<?php

namespace App\Filament\Resources\PointSettings;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\PointSettings\Pages\ManagePointSettings;
use App\Models\PointSetting;
use App\Models\RegistrationRewardSetting;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PointSettingResource extends AuthorizedResource
{
    protected static ?string $model = PointSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Referral & Rewards');
    }

    public static function getModelLabel(): string
    {
        return __('point-settings.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('point-settings.plural_model_label');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('point-settings.sections.rate.title'))
                    ->description(__('point-settings.sections.rate.description'))
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextInput::make('points_to_egp_rate')
                            ->label(__('point-settings.fields.points_to_egp_rate.label'))
                            // ->helperText(__('point-settings.fields.points_to_egp_rate.helper'))
                            ->required()
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0.0001)
                            ->maxValue(9999.9999)
                            ->prefix('EGP')
                            ->suffix(__('point-settings.fields.points_to_egp_rate.suffix'))
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, $set): void {
                                $rate = (float) $state;

                                if ($rate <= 0) {
                                    return;
                                }

                                $set('example_100_egp', number_format(100 / $rate, 0) . ' ' . __('point-settings.units.points'));
                                $set('example_1000_egp', number_format(1000 / $rate, 0) . ' ' . __('point-settings.units.points'));
                                $set('example_100_points', number_format(100 * $rate, 2) . ' ' . __('point-settings.units.egp'));
                                $set('example_1000_points', number_format(1000 * $rate, 2) . ' ' . __('point-settings.units.egp'));
                            })
                            ->columnSpanFull(),

                        Placeholder::make('current_rate_summary')
                            ->label(__('point-settings.placeholders.summary.label'))
                            ->content(function (Get $get): string {
                                $liveRate = $get('points_to_egp_rate');
                                $rate = (float) ($liveRate ?: PointSetting::getCurrentRate());

                                if ($rate <= 0) {
                                    return __('point-settings.placeholders.summary.invalid_rate');
                                }

                                return __('point-settings.placeholders.summary.content', [
                                    'rate' => number_format($rate, 4),
                                    'points' => number_format((int) ceil(100 / $rate)),
                                    'egp' => number_format(1000 * $rate, 2),
                                ]);
                            })
                            ->columnSpanFull(),

                        Placeholder::make('example_100_egp')
                            ->label(__('point-settings.placeholders.example_100_egp.label'))
                            ->content(function (Get $get): string {
                                $liveRate = $get('points_to_egp_rate');
                                $rate = (float) ($liveRate ?: PointSetting::getCurrentRate());

                                return $rate > 0 ? number_format(100 / $rate, 0) . ' ' . __('point-settings.units.points') : '-';
                            }),

                        Placeholder::make('example_1000_egp')
                            ->label(__('point-settings.placeholders.example_1000_egp.label'))
                            ->content(function (Get $get): string {
                                $liveRate = $get('points_to_egp_rate');
                                $rate = (float) ($liveRate ?: PointSetting::getCurrentRate());

                                return $rate > 0 ? number_format(1000 / $rate, 0) . ' ' . __('point-settings.units.points') : '-';
                            }),

                        Placeholder::make('example_100_points')
                            ->label(__('point-settings.placeholders.example_100_points.label'))
                            ->content(function (Get $get): string {
                                $liveRate = $get('points_to_egp_rate');
                                $rate = (float) ($liveRate ?: PointSetting::getCurrentRate());

                                return number_format(100 * $rate, 2) . ' ' . __('point-settings.units.egp');
                            }),

                        Placeholder::make('example_1000_points')
                            ->label(__('point-settings.placeholders.example_1000_points.label'))
                            ->content(function (Get $get): string {
                                $liveRate = $get('points_to_egp_rate');
                                $rate = (float) ($liveRate ?: PointSetting::getCurrentRate());

                                return number_format(1000 * $rate, 2) . ' ' . __('point-settings.units.egp');
                            }),
                    ])
                    ->columns(2),

                Section::make(__('point-settings.sections.notes.title'))
                    ->description(__('point-settings.sections.notes.description'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('reason_visible')
                            ->label(__('point-settings.fields.reason_visible.label'))
                            ->helperText(__('point-settings.fields.reason_visible.helper'))
                            ->rows(2)
                            ->maxLength(500)
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $state): void {
                                $component->state($state ?: __('point-settings.defaults.reason'));
                            })
                            ->afterStateUpdated(fn ($state, $set) => $set('reason', $state ?: __('point-settings.defaults.reason')))
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label(__('point-settings.fields.notes.label'))
                            ->helperText(__('point-settings.fields.notes.helper'))
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Hidden::make('reason')
                            ->default(__('point-settings.defaults.reason')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('points_to_egp_rate')
                //     ->label(__('point-settings.table.current_rate'))
                //     ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . ' ' . __('point-settings.table.rate_format_suffix'))
                //     ->badge()
                //     ->color('success')
                //     ->description(fn (): string => __('point-settings.table.current_rate_description', [
                //         'points' => number_format(PointSetting::calculatePointsNeeded(100)),
                //     ])),

                TextColumn::make('conversion_preview')
                    ->label(__('point-settings.table.quick_preview'))
                    ->state(fn (PointSetting $record): string => number_format(1000 * (float) $record->points_to_egp_rate, 2) . ' ' . __('point-settings.units.egp'))
                    ->description(__('point-settings.table.quick_preview_description')),

                TextColumn::make('registration_reward')
                    ->label(__('Registration Reward'))
                    ->state(fn (): string => number_format(RegistrationRewardSetting::getPoints()) . ' ' . __('point-settings.units.points')),

                TextColumn::make('visit_reward')
                    ->label(__('Visit Reward'))
                    ->state(fn (): string => number_format((int) Setting::getValue('visit_points_reward', 10)) . ' ' . __('point-settings.units.points'))
                    ->description(__('Per approved visit'))
                    ->badge()
                    ->color('primary'),

                TextColumn::make('subscription_rewards')
                    ->label(__('Subscription Rewards'))
                    ->state(function (): string {
                        $configuredPlans = SubscriptionPlan::query()
                            ->where('subscription_reward_points', '>', 0)
                            ->count();

                        return $configuredPlans > 0
                            ? __('Configured per plan')
                            : __('No plan rewards configured');
                    })
                    ->description(function (): string {
                        $configuredPlans = SubscriptionPlan::query()
                            ->where('subscription_reward_points', '>', 0)
                            ->count();

                        if ($configuredPlans === 0) {
                            return 'Open Subscription Plans to assign reward points for each package.';
                        }

                        return number_format($configuredPlans) . __('plan(s) currently grant subscription reward points.');
                    }),

                TextColumn::make('notes')
                    ->label(__('point-settings.table.latest_notes'))
                    ->limit(70)
                    ->placeholder(__('point-settings.table.no_notes')),

                TextColumn::make('updated_at')
                    ->label(__('point-settings.table.last_updated'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->actions([
                EditAction::make()
                    ->label(__('point-settings.actions.edit_rate'))
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalWidth('3xl')
                    ->modalHeading(__('point-settings.actions.edit_modal_heading'))
                    ->modalDescription(__('point-settings.actions.edit_modal_description'))
                    ->modalSubmitActionLabel(__('point-settings.actions.save_changes'))
                    ->successNotificationTitle(__('point-settings.actions.edit_success')),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->paginated(false)
            ->striped(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePointSettings::route('/'),
            'history' => Pages\ViewPointRateHistory::route('/history'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest();
    }
}
