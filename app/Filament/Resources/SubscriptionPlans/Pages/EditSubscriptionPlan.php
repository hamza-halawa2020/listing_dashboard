<?php

namespace App\Filament\Resources\SubscriptionPlans\Pages;

use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSubscriptionPlan extends EditRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => SubscriptionPlanResource::canDelete($this->getRecord()))
                ->before(function (DeleteAction $action): void {
                    if ($this->getRecord()->subscriptions()->exists()) {
                        Notification::make()
                            ->title(__('Cannot delete this plan'))
                            ->body(__('This subscription plan has active subscriptions linked to it and cannot be deleted.'))
                            ->danger()
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
