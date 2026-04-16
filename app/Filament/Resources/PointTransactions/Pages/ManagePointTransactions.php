<?php

namespace App\Filament\Resources\PointTransactions\Pages;

use App\Filament\Resources\PointTransactions\PointTransactionResource;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\ReferralService;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Pages\ManageRecords;

class ManagePointTransactions extends ManageRecords
{
    protected static string $resource = PointTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Adjust Points'))
                ->icon('heroicon-o-adjustments-horizontal')
                ->modalWidth('lg')
                ->visible(fn (): bool => PointTransactionResource::canCreate())
                ->createAnother(false)
                ->form([
                    Select::make('user_id')
                        ->label(__('User'))
                        ->options(
                            User::query()
                                ->whereIn('role', ['member', 'service_provider'])
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $u) => [
                                    $u->id => "{$u->name} — " . number_format($u->points_balance) . ' ' . __('points'),
                                ])
                        )
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, $set) => $set('_balance', $state
                            ? (int) User::find($state)?->points_balance
                            : null
                        )),

                    Placeholder::make('_balance')
                        ->label(__('Current Balance'))
                        ->content(fn (Get $get): string => $get('user_id')
                            ? number_format((int) User::find($get('user_id'))?->points_balance) . ' ' . __('points')
                            : '—'
                        )
                        ->visible(fn (Get $get): bool => (bool) $get('user_id')),

                    Radio::make('type')
                        ->label(__('Operation'))
                        ->options([
                            'admin_add'    => __('Add points'),
                            'admin_deduct' => __('Deduct points'),
                        ])
                        ->descriptions([
                            'admin_add'    => __('Increase the user\'s balance'),
                            'admin_deduct' => __('Decrease the user\'s balance'),
                        ])
                        ->default('admin_add')
                        ->required()
                        ->live()
                        ->inline(false),

                    TextInput::make('points')
                        ->label(__('Points'))
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->live(debounce: 500)
                        ->suffix(__('points')),

                    Placeholder::make('_preview')
                        ->label(__('Balance After'))
                        ->content(function (Get $get): string {
                            $userId = $get('user_id');
                            $pts    = (int) $get('points');
                            $type   = $get('type');

                            if (! $userId || ! $pts) {
                                return '—';
                            }

                            $current = (int) User::find($userId)?->points_balance;
                            $after   = $type === 'admin_deduct'
                                ? $current - $pts
                                : $current + $pts;

                            if ($after < 0) {
                                return '⚠️ ' . __('Insufficient balance') . " ({$current} " . __('points') . ')';
                            }

                            $arrow = $type === 'admin_deduct' ? '▼' : '▲';
                            return "{$current} → {$after} {$arrow} " . __('points');
                        })
                        ->visible(fn (Get $get): bool => (bool) $get('user_id') && (bool) $get('points')),

                    Textarea::make('note')
                        ->label(__('Note'))
                        ->placeholder(__('Optional reason or note for this adjustment'))
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->using(function (array $data): PointTransaction {
                    $user      = User::query()->findOrFail($data['user_id']);
                    $direction = $data['type'] === 'admin_deduct' ? -1 : 1;
                    $points    = ((int) $data['points']) * $direction;

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
