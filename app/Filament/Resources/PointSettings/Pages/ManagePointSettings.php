<?php

namespace App\Filament\Resources\PointSettings\Pages;

use App\Filament\Resources\PointSettings\PointSettingResource;
use App\Models\PointSetting;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManagePointSettings extends ManageRecords
{
    protected static string $resource = PointSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_history')
                ->label('View Rate History')
                ->icon('heroicon-o-clock')
                ->url(fn () => PointSettingResource::getUrl('history')),

            Actions\Action::make('test_calculations')
                ->label('Test Calculations')
                ->icon('heroicon-o-calculator')
                ->form([
                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label('Amount (EGP)')
                        ->numeric()
                        ->default(1000)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $amount = (float) $data['amount'];
                    $points = PointSetting::calculatePointsNeeded($amount);
                    $rate = PointSetting::getCurrentRate();

                    Notification::make()
                        ->title('Calculation Result')
                        ->body("{$amount} EGP = {$points} points (Rate: {$rate} EGP/point)")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function mount(): void
    {
        // Ensure we always have at least one record
        if (!PointSetting::exists()) {
            PointSetting::create([
                'points_to_egp_rate' => 0.1000,
                'notes' => 'Initial setup: 1 point = 10 piasters',
            ]);
        }
    }
}
