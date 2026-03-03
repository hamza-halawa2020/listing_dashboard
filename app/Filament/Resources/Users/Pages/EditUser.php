<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Support\FamilyMemberSubscription;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (is_array($data['familyMembers'] ?? null)) {
            FamilyMemberSubscription::validateStateForUser(
                $this->record,
                $data['familyMembers'],
            );
        }

        return $data;
    }
}
