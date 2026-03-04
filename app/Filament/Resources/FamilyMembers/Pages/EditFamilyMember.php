<?php

namespace App\Filament\Resources\FamilyMembers\Pages;

use App\Filament\Resources\FamilyMembers\FamilyMemberResource;
use App\Models\User;
use App\Support\FamilyMemberSubscription;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFamilyMember extends EditRecord
{
    protected static string $resource = FamilyMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => FamilyMemberResource::canDelete($this->getRecord())),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = User::findOrFail($data['user_id']);

        FamilyMemberSubscription::ensureValidForUser(
            $user,
            $data['subscription_id'] ?? null,
            $this->record,
        );

        return $data;
    }
}
