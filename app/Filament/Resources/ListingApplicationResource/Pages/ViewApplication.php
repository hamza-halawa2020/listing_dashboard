<?php

namespace App\Filament\Resources\ListingApplicationResource\Pages;

use App\Filament\Resources\ListingApplicationResource;
use App\Models\ListingApplication;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ListingApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label(__('Approve & Activate Listing'))
                ->icon('heroicon-o-check-circle')
                ->visible(fn (ListingApplication $record) => $record->status === 'pending')
                ->color('success')
                ->action(function (ListingApplication $record) {
                    try {
                        $service = app(\App\Services\ListingApplicationService::class);
                        $service->approveApplication($record);

                        Notification::make()
                            ->title(__('Application Approved'))
                            ->body(__('The listing has been activated'))
                            ->success()
                            ->send();

                        $this->redirect(ListingApplicationResource::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(__('Error'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('reject')
                ->label(__('Reject Application'))
                ->icon('heroicon-o-x-circle')
                ->visible(fn (ListingApplication $record) => $record->status === 'pending')
                ->color('danger')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label(__('Rejection Reason'))
                        ->required(),
                ])
                ->action(function (array $data, ListingApplication $record) {
                    try {
                        $service = app(\App\Services\ListingApplicationService::class);
                        $service->rejectApplication($record, $data['rejection_reason']);

                        Notification::make()
                            ->title(__('Application Rejected'))
                            ->body(__('The applicant will be notified'))
                            ->success()
                            ->send();

                        $this->redirect(ListingApplicationResource::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(__('Error'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
