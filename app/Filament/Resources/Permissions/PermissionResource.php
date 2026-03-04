<?php

namespace App\Filament\Resources\Permissions;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\Permissions\Pages\ManagePermissions;
use App\Support\AdminPermissionRegistry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends AuthorizedResource
{
    protected static ?string $model = Permission::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedLockClosed;

    public static function getModelLabel(): string
    {
        return __('Permission');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Permissions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Access Control');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (?Permission $record): bool => $record instanceof Permission
                        && in_array($record->name, AdminPermissionRegistry::allPermissions(), true)),
                Hidden::make('guard_name')
                    ->default('web'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => AdminPermissionRegistry::permissionLabel($state)),
                TextColumn::make('name')
                    ->label(__('Permission Key'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('guard_name')
                    ->label(__('Guard Name'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Permission $record): bool => static::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (Permission $record): bool => static::canDelete($record))
                    ->disabled(fn (Permission $record): bool => in_array($record->name, AdminPermissionRegistry::allPermissions(), true)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePermissions::route('/'),
        ];
    }
}
