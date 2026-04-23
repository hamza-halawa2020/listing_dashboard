<?php

namespace App\Filament\Resources\Visits;

use App\Filament\Resources\Visits\Pages\ListVisits;
use App\Filament\Resources\Visits\Pages\ViewVisit;
use App\Models\Visit;
use App\Services\ReferralService;
use App\Services\SystemNotificationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return __('Visits & Rewards');
    }

    public static function getModelLabel(): string
    {
        return __('Visit');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Visits');
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = Visit::where('status', Visit::STATUS_PENDING)->count();
            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Visit::query()->with(['user', 'listing', 'attachments']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->description(fn (Visit $r): string => $r->user?->phone ?? ''),

                TextColumn::make('listing.name')
                    ->label(__('Listing'))
                    ->searchable()
                    ->limit(30)
                    ->description(fn (Visit $r): string => $r->listing?->address ?? ''),

                TextColumn::make('service_type')
                    ->label(__('Service'))
                    ->formatStateUsing(fn (string $state): string => Visit::SERVICE_TYPES[$state] ?? $state)
                    ->badge()
                    ->color('info'),

                TextColumn::make('attachments_count')
                    ->label(__('Files'))
                    ->counts('attachments')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Visit::STATUS_PENDING  => 'warning',
                        Visit::STATUS_APPROVED => 'success',
                        Visit::STATUS_REJECTED => 'danger',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Visit::STATUS_PENDING  => __('Pending'),
                        Visit::STATUS_APPROVED => __('Approved'),
                        Visit::STATUS_REJECTED => __('Rejected'),
                        default                => $state,
                    }),

                TextColumn::make('points_reward')
                    ->label(__('Points'))
                    ->state(fn (Visit $r): string => $r->status === Visit::STATUS_APPROVED
                        ? '+' . Visit::getVisitPoints() . ' pts'
                        : Visit::getVisitPoints() . ' pts'
                    )
                    ->color(fn (Visit $r): string => $r->status === Visit::STATUS_APPROVED ? 'success' : 'gray'),

                TextColumn::make('visited_at')
                    ->label(__('Visited At'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordUrl(fn (Visit $r): string => VisitResource::getUrl('view', ['record' => $r]))
            ->actions([
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Visit $r): bool => $r->status === Visit::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading(__('Approve Visit & Grant Points'))
                    ->modalDescription(fn (Visit $r): string => __(
                        __('This will grant :points points to :user.'),
                        ['points' => Visit::getVisitPoints(), 'user' => $r->user?->name ?? '']
                    ))
                    ->action(function (Visit $record, ReferralService $referralService, SystemNotificationService $notifications): void {
                        $record->update([
                            'status'               => Visit::STATUS_APPROVED,
                            'approved_by_admin_id' => auth()->id(),
                            'approved_at'          => now(),
                        ]);

                        $referralService->addPoints(
                            $record->user,
                            Visit::getVisitPoints(),
                            'visit_bonus',
                            null,
                            auth()->id(),
                            __('Visit approved: :listing', ['listing' => $record->listing?->name]),
                        );

                        // Notify the user
                        $notifications->notifyUser(
                            $record->user,
                            __('Visit approved! 🎉'),
                            __('Your visit to :listing has been approved. :points points have been added to your balance.', [
                                'listing' => $record->listing?->name,
                                'points'  => Visit::getVisitPoints(),
                            ]),
                            'success',
                        );

                        // Notify all admins
                        $notifications->notifyAdmins(
                            __('Visit approved'),
                            __(':admin approved :user\'s visit to :listing and granted :points points.', [
                                'admin'   => auth()->user()?->name,
                                'user'    => $record->user?->name,
                                'listing' => $record->listing?->name,
                                'points'  => Visit::getVisitPoints(),
                            ]),
                            'success',
                        );

                        Notification::make()
                            ->title(__('Visit approved and points granted.'))
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Visit $r): bool => $r->status === Visit::STATUS_PENDING)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label(__('Rejection Reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Visit $record, array $data, SystemNotificationService $notifications): void {
                        $record->update([
                            'status'           => Visit::STATUS_REJECTED,
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        // Notify the user
                        $notifications->notifyUser(
                            $record->user,
                            __('Visit rejected'),
                            __('Your visit to :listing has been rejected. Reason: :reason', [
                                'listing' => $record->listing?->name,
                                'reason'  => $data['rejection_reason'],
                            ]),
                            'danger',
                        );

                        // Notify all admins
                        $notifications->notifyAdmins(
                            __('Visit rejected'),
                            __(':admin rejected :user\'s visit to :listing. Reason: :reason', [
                                'admin'   => auth()->user()?->name,
                                'user'    => $record->user?->name,
                                'listing' => $record->listing?->name,
                                'reason'  => $data['rejection_reason'],
                            ]),
                            'warning',
                        );

                        Notification::make()
                            ->title(__('Visit rejected.'))
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisits::route('/'),
            'view'  => ViewVisit::route('/{record}'),
        ];
    }
}
