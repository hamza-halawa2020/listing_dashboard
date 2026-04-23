<?php

namespace App\Filament\Resources\PointSettings\Pages;

use App\Filament\Resources\PointSettings\PointSettingResource;
use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use App\Models\PointSetting;
use App\Models\RegistrationRewardHistory;
use App\Models\RegistrationRewardSetting;
use App\Models\Setting;
use App\Models\VisitPointRewardHistory;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Validation\ValidationException;

class ManagePointSettings extends ManageRecords
{
    protected static string $resource = PointSettingResource::class;

    public function getTitle(): string
    {
        return __('point-settings.page.title');
    }

    // public function getSubheading(): ?string
    // {
    //     $rate = PointSetting::getCurrentRate();

    //     return __('point-settings.page.subheading', [
    //         'rate' => number_format($rate, 4),
    //     ]);
    // }

    public function mount(): void
    {
        if (! PointSetting::exists()) {
            PointSetting::create([
                'points_to_egp_rate' => 0.1000,
                'notes' => __('point-settings.defaults.initial_notes'),
            ]);
        }

        RegistrationRewardSetting::getOrCreateDefault();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_history')
                ->label(__('point-settings.header_actions.history'))
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->tooltip(__('point-settings.header_actions.history_tooltip'))
                ->url(fn () => PointSettingResource::getUrl('history')),

            Actions\Action::make('edit_registration_reward')
                ->label(__('Edit Registration Reward'))
                ->icon('heroicon-o-gift')
                ->color('warning')
                ->modalWidth('lg')
                ->modalHeading(__('Edit Registration Reward'))
                ->modalDescription(__('Update the registration reward points separately and keep a full history of changes.'))
                ->modalSubmitActionLabel(__('Save Registration Reward'))
                ->fillForm(function (): array {
                    $setting = RegistrationRewardSetting::getOrCreateDefault();

                    return [
                        'points' => $setting->points,
                        'reason' => 'Updated from admin panel',
                        'notes' => $setting->notes,
                    ];
                })
                ->form([
                    TextInput::make('points')
                        ->label(__('Registration Reward Points'))
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->step(1),
                    Textarea::make('reason')
                        ->label(__('Reason for Change'))
                        ->rows(2)
                        ->required()
                        ->maxLength(500),
                    Textarea::make('notes')
                        ->label(__('Additional Notes'))
                        ->rows(3)
                        ->maxLength(1000),
                ])
                ->action(function (array $data): void {
                    $setting = RegistrationRewardSetting::getOrCreateDefault();
                    $oldPoints = (int) $setting->points;
                    $newPoints = max((int) $data['points'], 0);

                    if ($oldPoints === $newPoints && (($setting->notes ?? null) === ($data['notes'] ?? null))) {
                        throw ValidationException::withMessages([
                            'points' => __('The new registration reward must be different from the current value.'),
                        ]);
                    }

                    RegistrationRewardHistory::create([
                        'old_points' => $oldPoints,
                        'new_points' => $newPoints,
                        'reason' => $data['reason'],
                        'changed_by_admin_id' => auth()->id(),
                    ]);

                    $setting->update([
                        'points' => $newPoints,
                        'notes' => $data['notes'] ?? null,
                    ]);

                    Notification::make()
                        ->title(__('Registration reward updated successfully'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('manage_subscription_rewards')
                ->label(__('Manage Subscription Rewards'))
                ->icon('heroicon-o-queue-list')
                ->color('success')
                ->tooltip(__('Subscription reward points are configured separately for each subscription plan.'))
                ->url(fn (): string => SubscriptionPlanResource::getUrl('index')),

            Actions\Action::make('edit_visit_reward')
                ->label(__('Visit Points Reward'))
                ->icon('heroicon-o-building-office-2')
                ->color('primary')
                ->modalWidth('sm')
                ->modalHeading(__('Visit Points Reward'))
                ->modalDescription(__('Points granted to the user when a visit is approved by admin.'))
                ->modalSubmitActionLabel(__('Save'))
                ->fillForm(fn (): array => [
                    'visit_points_reward' => (int) Setting::getValue('visit_points_reward', 10),
                    'reason'              => '',
                ])
                ->form([
                    TextInput::make('visit_points_reward')
                        ->label(__('Points per approved visit'))
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(10)
                        ->suffix('pts'),
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label(__('Reason for change'))
                        ->rows(2)
                        ->maxLength(500)
                        ->placeholder(__('Optional — explain why you changed this value')),
                ])
                ->action(function (array $data): void {
                    $oldPoints = (int) Setting::getValue('visit_points_reward', 10);
                    $newPoints = (int) ($data['visit_points_reward'] ?? 10);

                    VisitPointRewardHistory::create([
                        'old_points'          => $oldPoints,
                        'new_points'          => $newPoints,
                        'reason'              => $data['reason'] ?? null,
                        'changed_by_admin_id' => auth()->id(),
                    ]);

                    Setting::setValue('visit_points_reward', $newPoints);

                    Notification::make()
                        ->title(__('Visit points reward updated successfully'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('test_calculations')
                ->label(__('point-settings.header_actions.calculator'))
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->tooltip(__('point-settings.header_actions.calculator_tooltip'))
                ->modalHeading(__('point-settings.calculator.heading'))
                ->modalDescription(__('point-settings.calculator.description'))
                ->modalSubmitActionLabel(__('point-settings.calculator.submit'))
                ->modalWidth('lg')
                ->form([
                    TextInput::make('amount_egp')
                        ->label(__('point-settings.calculator.amount_egp'))
                        ->numeric()
                        ->default(1000)
                        ->minValue(0.01)
                        ->prefix('EGP')
                        ->helperText(__('point-settings.calculator.amount_egp_helper'))
                        ->required(),

                    TextInput::make('amount_points')
                        ->label(__('point-settings.calculator.amount_points'))
                        ->numeric()
                        ->default(1000)
                        ->minValue(1)
                        ->suffix(__('point-settings.units.point'))
                        ->helperText(__('point-settings.calculator.amount_points_helper'))
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $rate = PointSetting::getCurrentRate();
                    $egp = (float) $data['amount_egp'];
                    $pts = (int) $data['amount_points'];
                    $egpToPoints = PointSetting::calculatePointsNeeded($egp);
                    $ptsToEgp = PointSetting::calculateEgpValue($pts);

                    Notification::make()
                        ->title(__('point-settings.calculator.result_title'))
                        ->body(__('point-settings.calculator.result_body', [
                            'rate' => number_format($rate, 4),
                            'egp' => number_format($egp, 2),
                            'egp_points' => number_format($egpToPoints),
                            'points' => number_format($pts),
                            'points_egp' => number_format($ptsToEgp, 2),
                            'point_word' => __('point-settings.units.points'),
                            'egp_word' => __('point-settings.units.egp'),
                        ]))
                        ->success()
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
