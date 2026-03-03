<?php

namespace App\Filament\Resources\FamilyMembers\Pages;

use App\Filament\Resources\FamilyMembers\FamilyMemberResource;
use App\Models\User;
use App\Support\FamilyMemberSubscription;
use Filament\Resources\Pages\CreateRecord;

class CreateFamilyMember extends CreateRecord
{
    protected static string $resource = FamilyMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::findOrFail($data['user_id']);

        FamilyMemberSubscription::ensureValidForUser(
            $user,
            $data['subscription_id'] ?? null,
        );

        return $data;
    }
}
