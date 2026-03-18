<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\AuthorizedResource;
use App\Models\User;
use App\Support\UserRecordProtection;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class UserResource extends AuthorizedResource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?int $navigationSort = 9;
    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function getEditAuthorizationResponse(Model $record): Response
    {
        if (! $record instanceof User) {
            return Response::deny();
        }

        $permissionResponse = parent::getEditAuthorizationResponse($record);

        if ($permissionResponse->denied()) {
            return $permissionResponse;
        }

        return UserRecordProtection::editResponse(auth()->user(), $record);
    }

    public static function getDeleteAuthorizationResponse(Model $record): Response
    {
        if (! $record instanceof User) {
            return Response::deny();
        }

        $permissionResponse = parent::getDeleteAuthorizationResponse($record);

        if ($permissionResponse->denied()) {
            return $permissionResponse;
        }

        return UserRecordProtection::deleteResponse(auth()->user(), $record);
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
