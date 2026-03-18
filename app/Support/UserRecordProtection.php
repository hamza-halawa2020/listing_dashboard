<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;

class UserRecordProtection
{
    public static function editResponse(Authenticatable | null $actor, User $record): Response
    {
        $actor = $actor instanceof User ? $actor : null;

        if ($record->hasRole('super_admin') && (! $actor || ! $actor->is($record))) {
            return Response::deny(__('user-protection.only_this_super_admin_can_be_edited'));
        }

        return Response::allow();
    }

    public static function deleteResponse(Authenticatable | null $actor, User $record): Response
    {
        $actor = $actor instanceof User ? $actor : null;

        if ($actor?->is($record)) {
            return Response::deny(__('user-protection.you_cannot_delete_your_own_account'));
        }

        if ($record->hasRole('super_admin')) {
            return Response::deny(__('user-protection.only_this_super_admin_can_be_deleted'));
        }

        return Response::allow();
    }
}
