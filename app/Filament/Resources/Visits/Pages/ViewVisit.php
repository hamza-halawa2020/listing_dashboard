<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Filament\Resources\Visits\VisitResource;
use App\Models\Visit;
use App\Services\ReferralService;
use App\Services\SystemNotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewVisit extends ViewRecord
{
    protected static string $resource = VisitResource::class;

    public function getView(): string
    {
        return 'filament.resources.visits.pages.view-visit';
    }
    
    public function getTitle(): string
    {
        return __('Visit') . ' #' . $this->record->id;
    }

    protected function getHeaderActions(): array
    {
        return [

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
            'visit' => $this->record->load(['user', 'listing', 'attachments', 'approvedByAdmin']),
        ];
    }
}
