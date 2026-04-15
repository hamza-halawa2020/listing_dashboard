<?php

namespace App\Filament\Resources\PointTransactions;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\PointTransactions\Pages\ManagePointTransactions;
use App\Models\PointTransaction;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PointTransactionResource extends AuthorizedResource
{
    protected static ?string $model = PointTransaction::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 11;

    public static function getModelLabel(): string
    {
        return __('Point Transaction');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Point Transactions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Referral & Rewards');
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'user',
                'referral',
                'createdByAdmin',
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('User'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Select::make('type')
                    ->label(__('Adjustment Type'))
                    ->options([
                        'admin_add' => __('Admin add'),
                        'admin_deduct' => __('Admin deduct'),
                    ])
                    ->native(false)
                    ->required(),
                TextInput::make('points')
                    ->label(__('Points'))
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Textarea::make('note')
                    ->label(__('Note'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $typeLabels = PointTransaction::typeLabels();

        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'referral_bonus', 'referee_bonus', 'admin_add' => 'success',
                        'admin_deduct', 'redeem', 'expire' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => $typeLabels[$state] ?? $state),
                TextColumn::make('points')
                    ->label(__('Points'))
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? '+' . $state : (string) $state)
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('balance_after')
                    ->label(__('Balance After'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('referral.id')
                    ->label(__('Referral #'))
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('createdByAdmin.name')
                    ->label(__('Created By'))
                    ->placeholder(__('System'))
                    ->sortable(),
                TextColumn::make('note')
                    ->label(__('Note'))
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options($typeLabels),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePointTransactions::route('/'),
        ];
    }
}
