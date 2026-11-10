<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Exports\Exporters\UserExporter;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\AdminPermissionRegistry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email address'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                TextColumn::make('referral_code')
                    ->label(__('Referral Code'))
                    ->searchable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state, $record) => $record->payments()->where('status', 'completed')->exists() ? $state : '-')
                    ->placeholder('-'),
                TextColumn::make('points_balance')
                    ->label(__('Points'))
                    ->numeric()
                    ->sortable()
                    ->badge(),
                TextColumn::make('referrer.name')
                    ->label(__('Referred By'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('role')
                    ->label(__('Role'))
                    ->badge(),
                TextColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->formatStateUsing(function ($state): string {
                        $roles = is_array($state) ? $state : [$state];

                        return collect($roles)
                            ->filter()
                            ->map(fn (string $role): string => AdminPermissionRegistry::roleLabel($role))
                            ->implode(', ');
                    })
                    ->wrap(),
                TextColumn::make('national_id')
                    ->label(__('National ID'))
                    ->searchable(),
                TextColumn::make('location.name')
                    ->label(__('Location'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label(__('Birth Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label(__('Gender'))
                    ->badge(),
                TextColumn::make('email_verified_at')
                    ->label(__('Email Verified At'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('admins')
                    ->label(__('Admins'))
                    ->query(fn (Builder $query): Builder => $query
                        ->whereHas('roles', fn (Builder $q): Builder => $q
                            ->whereIn('name', AdminPermissionRegistry::panelRoles()))),

                Filter::make('subscribers_with_payments')
                    ->label(__('Subscribers (Paid)'))
                    ->query(fn (Builder $query): Builder => $query
                        ->whereDoesntHave('roles', fn (Builder $q): Builder => $q
                            ->whereIn('name', AdminPermissionRegistry::panelRoles()))
                        ->whereHas('subscriptions')
                        ->whereHas('payments', fn (Builder $q): Builder => $q
                            ->where('status', 'completed'))),

                Filter::make('non_subscribers')
                    ->label(__('Non-Subscribers'))
                    ->query(fn (Builder $query): Builder => $query
                        ->whereDoesntHave('roles', fn (Builder $q): Builder => $q
                            ->whereIn('name', AdminPermissionRegistry::panelRoles()))
                        ->whereDoesntHave('subscriptions')),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record): bool => UserResource::canEdit($record)),
            ])
            ->toolbarActions([
                ExportAction::make()->exporter(UserExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords()
                        ->visible(fn (): bool => UserResource::canDeleteAny()),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (User $record): bool => UserResource::canDelete($record),
            );
    }
}
