<?php

namespace App\Filament\Resources\MembershipCards;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\MembershipCards\Pages\ManageMembershipCards;
use App\Models\MembershipCard;
use App\Models\SubscriptionPlan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MembershipCardResource extends AuthorizedResource
{
    protected static ?string $model = MembershipCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 7;

    public static function getModelLabel(): string
    {
        return __('Membership Card');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Membership Cards');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('card_number')
                    ->label(__('Card Number'))
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Select::make('subscription_plan_id')
                    ->label(__('Subscription Plan'))
                    ->relationship('subscriptionPlan', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('starts_at')
                    ->label(__('Starts At'))
                    ->required(),
                DatePicker::make('ends_at')
                    ->label(__('Ends At'))
                    ->required(),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        MembershipCard::STATUS_UNASSIGNED => MembershipCard::statusLabel(MembershipCard::STATUS_UNASSIGNED),
                        MembershipCard::STATUS_PENDING    => MembershipCard::statusLabel(MembershipCard::STATUS_PENDING),
                        MembershipCard::STATUS_ACTIVE     => MembershipCard::statusLabel(MembershipCard::STATUS_ACTIVE),
                        MembershipCard::STATUS_REJECTED   => MembershipCard::statusLabel(MembershipCard::STATUS_REJECTED),
                    ])
                    ->default(MembershipCard::STATUS_UNASSIGNED)
                    ->required(),
                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('card_number')
                    ->label(__('Card Number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subscriptionPlan.name')
                    ->label(__('Plan'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string =>
                        $state ? MembershipCard::statusLabel($state) : '-'
                    )
                    ->color(fn (?string $state): string =>
                        $state ? MembershipCard::statusColor($state) : 'gray'
                    )
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('national_id')
                    ->label(__('National ID'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('starts_at')
                    ->label(__('Starts At'))
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('Ends At'))
                    ->date()
                    ->sortable(),
                TextColumn::make('activation_requested_at')
                    ->label(__('Activation Requested At'))
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('activation_approved_at')
                    ->label(__('Activation Approved At'))
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label(__('Notes'))
                    ->placeholder('-')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        MembershipCard::STATUS_UNASSIGNED => MembershipCard::statusLabel(MembershipCard::STATUS_UNASSIGNED),
                        MembershipCard::STATUS_PENDING    => MembershipCard::statusLabel(MembershipCard::STATUS_PENDING),
                        MembershipCard::STATUS_ACTIVE     => MembershipCard::statusLabel(MembershipCard::STATUS_ACTIVE),
                        MembershipCard::STATUS_REJECTED   => MembershipCard::statusLabel(MembershipCard::STATUS_REJECTED),
                    ]),
                SelectFilter::make('subscription_plan_id')
                    ->label(__('Subscription Plan'))
                    ->relationship('subscriptionPlan', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                // ── Approve Activation ─────────────────────────────────
                Action::make('approve_activation')
                    ->label(__('Approve Activation'))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (MembershipCard $record): bool =>
                        static::canEdit($record) &&
                        $record->status === MembershipCard::STATUS_PENDING
                    )
                    ->fillForm(fn (MembershipCard $record): array => [
                        'card_number' => $record->card_number,
                        'national_id' => $record->national_id,
                    ])
                    ->form([
                        TextInput::make('card_number')
                            ->label(__('Card Number'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('national_id')
                            ->label(__('National ID (submitted by user)'))
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->label(__('Admin Notes (optional)'))
                            ->rows(2),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading(__('Approve Card Activation'))
                    ->modalDescription(__('This will create a subscription for the user and activate the card.'))
                    ->action(function (MembershipCard $record, array $data): void {
                        try {
                            $record->approveActivation($data['notes'] ?? null);

                            Notification::make()
                                ->title(__('Activation approved'))
                                ->body(__('Card :number is now active.', ['number' => $record->card_number]))
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title(__('Activation failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ── Reject Activation ──────────────────────────────────
                Action::make('reject_activation')
                    ->label(__('Reject Activation'))
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (MembershipCard $record): bool =>
                        static::canEdit($record) &&
                        $record->status === MembershipCard::STATUS_PENDING
                    )
                    ->form([
                        Textarea::make('reason')
                            ->label(__('Rejection Reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading(__('Reject Card Activation'))
                    ->action(function (MembershipCard $record, array $data): void {
                        $record->rejectActivation($data['reason']);

                        Notification::make()
                            ->title(__('Activation rejected'))
                            ->body(__('Card :number activation has been rejected.', ['number' => $record->card_number]))
                            ->warning()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(fn ($record): bool => static::canEdit($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canDeleteAny()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMembershipCards::route('/'),
        ];
    }
}
