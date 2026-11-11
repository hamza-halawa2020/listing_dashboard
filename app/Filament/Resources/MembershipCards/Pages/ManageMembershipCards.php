<?php

namespace App\Filament\Resources\MembershipCards\Pages;

use App\Filament\Resources\MembershipCards\MembershipCardResource;
use App\Models\MembershipCard;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageMembershipCards extends ManageRecords
{
    protected static string $resource = MembershipCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('issue_cards')
                ->label(__('Issue Cards'))
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->color('primary')
                ->visible(fn (): bool => MembershipCardResource::canCreate())
                ->fillForm(fn (): array => [
                    'prefix'        => 'CARD',
                    'from_number'   => 1,
                    'to_number'     => 1,
                    'starts_at'     => now()->format('Y-m-d'),
                ])
                ->form([
                    Select::make('subscription_plan_id')
                        ->label(__('Subscription Plan'))
                        ->options(fn () => \App\Models\SubscriptionPlan::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (callable $set, $state, callable $get) {
                            if ($state) {
                                $plan = \App\Models\SubscriptionPlan::find($state);
                                if ($plan?->code) {
                                    $set('prefix', strtoupper($plan->code));
                                }
                                //  ends_at من starts_at + duration_days
                                if ($plan?->duration_days) {
                                    $startsAt = $get('starts_at') ?: now()->format('Y-m-d');
                                    $set('ends_at', \Carbon\Carbon::parse($startsAt)->addDays($plan->duration_days)->format('Y-m-d'));
                                }
                            }
                        }),
                    TextInput::make('prefix')
                        ->label(__('Card Prefix'))
                        ->required()
                        ->maxLength(20)
                        ->helperText(__('E.g. GOLD → generates GOLD-00001, GOLD-00002 …')),
                    TextInput::make('from_number')
                        ->label(__('From Card Number'))
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->live(onBlur: true)
                        ->helperText(fn ($state, callable $get) => 
                            filled($get('prefix')) && filled($state)
                                ? __('First card: :card', ['card' => $get('prefix') . '-' . str_pad((string) $state, 5, '0', STR_PAD_LEFT)])
                                : ''
                        ),
                    TextInput::make('to_number')
                        ->label(__('To Card Number'))
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->live(onBlur: true)
                        ->helperText(function (callable $get): string {
                            $prefix = $get('prefix');
                            $from   = $get('from_number');
                            $to     = $get('to_number');
                            if (!filled($prefix) || !filled($from) || !filled($to) || $to < $from) {
                                return '';
                            }
                            $lastCard = $prefix . '-' . str_pad((string) $to, 5, '0', STR_PAD_LEFT);
                            $count    = $to - $from + 1;
                            return __('Last card: :last — Total: :count card(s)', [
                                'last'  => $lastCard,
                                'count' => $count,
                            ]);
                        })
                        ->rule(function (callable $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $from = (int) $get('from_number');
                                $to   = (int) $value;
                                if ($to < $from) {
                                    $fail(__('The "To" number must be greater than or equal to the "From" number.'));
                                }
                                if (($to - $from + 1) > 500) {
                                    $fail(__('Maximum 500 cards per batch.'));
                                }
                            };
                        }),
                    DatePicker::make('starts_at')
                        ->label(__('Starts At'))
                        ->required()
                        ->default(now())
                        ->live()
                        ->afterStateUpdated(function (callable $set, $state, callable $get) {
                            $planId = $get('subscription_plan_id');
                            if ($planId && $state) {
                                $plan = \App\Models\SubscriptionPlan::find($planId);
                                if ($plan?->duration_days) {
                                    $set('ends_at', \Carbon\Carbon::parse($state)->addDays($plan->duration_days)->format('Y-m-d'));
                                }
                            }
                        }),
                    DatePicker::make('ends_at')
                        ->label(__('Ends At'))
                        ->required()
                        ->helperText(__('Auto-calculated from plan duration. You can override it.')),
                ])
                ->action(function (array $data): void {
                    $prefix     = strtoupper(trim($data['prefix']));
                    $from       = (int) $data['from_number'];
                    $to         = (int) $data['to_number'];
                    $planId     = $data['subscription_plan_id'];
                    $startsAt   = $data['starts_at'];
                    $endsAt     = $data['ends_at'];
                    $createdBy  = auth()->id();

                    $created = 0;
                    $skipped = 0;

                    for ($num = $from; $num <= $to; $num++) {
                        $cardNum = $prefix . '-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);

                        if (MembershipCard::where('card_number', $cardNum)->exists()) {
                            $skipped++;
                            continue;
                        }

                        MembershipCard::create([
                            'card_number'           => $cardNum,
                            'subscription_plan_id'  => $planId,
                            'starts_at'             => $startsAt,
                            'ends_at'               => $endsAt,
                            'status'                => MembershipCard::STATUS_UNASSIGNED,
                            'created_by'            => $createdBy,
                        ]);
                        $created++;
                    }

                    Notification::make()
                        ->title(__('Batch complete'))
                        ->body(__('Created: :created card(s). Skipped (duplicate): :skipped.', [
                            'created' => $created,
                            'skipped' => $skipped,
                        ]))
                        ->success()
                        ->send();
                }),
        ];
    }
}
