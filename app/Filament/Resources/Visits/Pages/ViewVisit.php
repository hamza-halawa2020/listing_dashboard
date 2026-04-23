<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Resources\Visits\VisitResource;
use App\Models\Visit;
use App\Services\ReferralService;
use App\Services\SystemNotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ViewVisit extends Page
{
    protected static string $resource = VisitResource::class;

    // Livewire property — bound from route {record}
    public Visit $record;

    public function getView(): string
    {
        return 'filament.resources.visits.pages.view-visit';
    }

    public function getTitle(): string
    {
        return __('Visit') . ' #' . $this->record->id;
    }

    public function mount(int|string $record): void
    {
        $this->record = Visit::with(['user', 'listing', 'attachments', 'approvedByAdmin'])->findOrFail($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label(__('Approve & Grant Points'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === Visit::STATUS_PENDING)
                ->requiresConfirmation()
                ->modalHeading(__('Approve Visit & Grant Points'))
                ->modalDescription(fn (): string => __(
                    'This will grant :points points to :user.',
                    ['points' => Visit::getVisitPoints(), 'user' => $this->record->user?->name ?? '']
                ))
                ->action(function (ReferralService $referralService, SystemNotificationService $notifications): void {
                    $this->record->update([
                        'status'               => Visit::STATUS_APPROVED,
                        'approved_by_admin_id' => auth()->id(),
                        'approved_at'          => now(),
                    ]);

                    $referralService->addPoints(
                        $this->record->user,
                        Visit::getVisitPoints(),
                        'visit_bonus',
                        null,
                        auth()->id(),
                        __('Visit approved: :listing', ['listing' => $this->record->listing?->name]),
                    );

                    $notifications->notifyUser(
                        $this->record->user,
                        __('Visit approved! 🎉'),
                        __('Your visit to :listing has been approved. :points points have been added to your balance.', [
                            'listing' => $this->record->listing?->name,
                            'points'  => Visit::getVisitPoints(),
                        ]),
                        'success',
                    );

                    $notifications->notifyAdmins(
                        __('Visit approved'),
                        __(':admin approved :user\'s visit to :listing and granted :points points.', [
                            'admin'   => auth()->user()?->name,
                            'user'    => $this->record->user?->name,
                            'listing' => $this->record->listing?->name,
                            'points'  => Visit::getVisitPoints(),
                        ]),
                        'success',
                    );

                    Notification::make()->title(__('Visit approved and points granted.'))->success()->send();

                    // Reload record to reflect new status in UI
                    $this->record = $this->record->fresh(['user', 'listing', 'attachments', 'approvedByAdmin']);
                }),

            Action::make('reject')
                ->label(__('Reject'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === Visit::STATUS_PENDING)
                ->form([
                    Textarea::make('rejection_reason')
                        ->label(__('Rejection Reason'))
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data, SystemNotificationService $notifications): void {
                    $this->record->update([
                        'status'           => Visit::STATUS_REJECTED,
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    $notifications->notifyUser(
                        $this->record->user,
                        __('Visit rejected'),
                        __('Your visit to :listing has been rejected. Reason: :reason', [
                            'listing' => $this->record->listing?->name,
                            'reason'  => $data['rejection_reason'],
                        ]),
                        'danger',
                    );

                    $notifications->notifyAdmins(
                        __('Visit rejected'),
                        __(':admin rejected :user\'s visit to :listing. Reason: :reason', [
                            'admin'   => auth()->user()?->name,
                            'user'    => $this->record->user?->name,
                            'listing' => $this->record->listing?->name,
                            'reason'  => $data['rejection_reason'],
                        ]),
                        'warning',
                    );

                    Notification::make()->title(__('Visit rejected.'))->warning()->send();

                    $this->record = $this->record->fresh(['user', 'listing', 'attachments', 'approvedByAdmin']);
                }),

            Action::make('back')
                ->label(__('Back'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(VisitResource::getUrl()),
        ];
    }

    public function getViewData(): array
    {
        return [
            'visit' => $this->record,
        ];
    }
}
