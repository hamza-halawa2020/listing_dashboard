<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Support\AdminPermissionRegistry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends AuthorizedResource
{
    protected static ?string $model = Role::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public static function getModelLabel(): string
    {
        return __('Role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Roles');
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
                    ->disabled(fn (?Role $record): bool => $record instanceof Role
                        && in_array($record->name, AdminPermissionRegistry::panelRoles(), true)),
                Hidden::make('guard_name')
                    ->default('web'),
                Select::make('permissions')
                    ->label(__('Permissions'))
                    ->relationship('permissions', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => AdminPermissionRegistry::permissionLabel($record->name))
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => AdminPermissionRegistry::roleLabel($state)),
                TextColumn::make('permissions.name')
                    ->label(__('Permissions'))
                    ->formatStateUsing(fn ($state): string => collect(is_array($state) ? $state : [$state])
                        ->filter()
                        ->map(fn (string $permission): string => AdminPermissionRegistry::permissionLabel($permission))
                        ->implode(', '))
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Role $record): bool => static::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (Role $record): bool => static::canDelete($record))
                    ->disabled(fn (Role $record): bool => in_array($record->name, AdminPermissionRegistry::panelRoles(), true)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}
