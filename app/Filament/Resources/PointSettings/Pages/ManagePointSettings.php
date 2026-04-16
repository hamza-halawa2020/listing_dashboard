<?php

namespace App\Filament\Resources\PointSettings\Pages;

use App\Filament\Resources\PointSettings\PointSettingResource;
use App\Models\PointSetting;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManagePointSettings extends ManageRecords
{
    protected static string $resource = PointSettingResource::class;

    public function getTitle(): string
    {
        return __('point-settings.page.title');
    }

    public function getSubheading(): ?string
    {
        $rate = PointSetting::getCurrentRate();

        return __('point-settings.page.subheading', [
            'rate' => number_format($rate, 4),
        ]);
    }

    public function mount(): void
    {
        if (! PointSetting::exists()) {
            PointSetting::create([
                'points_to_egp_rate' => 0.1000,
                'notes' => __('point-settings.defaults.initial_notes'),
            ]);
        }

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
