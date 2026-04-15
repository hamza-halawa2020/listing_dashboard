<?php

namespace App\Filament\Resources\PointTransactions\Pages;

use App\Filament\Resources\PointTransactions\PointTransactionResource;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\ReferralService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePointTransactions extends ManageRecords
{
    protected static string $resource = PointTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Adjust Points'))
                ->visible(fn (): bool => PointTransactionResource::canCreate())
                ->createAnother(false)
                ->using(function (array $data): PointTransaction {
                    $user = User::query()->findOrFail($data['user_id']);
                    $direction = $data['type'] === 'admin_deduct' ? -1 : 1;
                    $points = ((int) $data['points']) * $direction;

                    return app(ReferralService::class)->addPoints(
                        $user,
                        $points,
                        $data['type'],
                        null,
                        auth()->id(),
                        $data['note'] ?? null,
                    );
                })
                ->successNotificationTitle(__('Point adjustment saved')),
        ];
    }
}
