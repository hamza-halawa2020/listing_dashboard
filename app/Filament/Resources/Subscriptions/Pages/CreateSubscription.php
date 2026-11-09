<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class CreateSubscription extends Page
{
    protected static string $resource = SubscriptionResource::class;

    protected string $view = 'filament.resources.subscriptions.pages.create-subscription';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'status'         => 'active',
            'payment_status' => 'completed',
            'starts_at'      => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(2)
            ->components([

                // ── User ─────────────────────────────────────────────────
                Select::make('user_id')
                    ->label(__('User'))
                    ->options(fn () => User::query()
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn ($u) => [$u->id => "{$u->name}" . ($u->phone ? " — {$u->phone}" : '')])
                        ->toArray()
                    )
                    ->getSearchResultsUsing(fn (string $search) => User::query()
                        ->where(fn ($q) => $q
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('national_id', 'like', "%{$search}%")
                        )
                        ->limit(30)
                        ->get()
                        ->mapWithKeys(fn ($u) => [$u->id => "{$u->name}" . ($u->phone ? " — {$u->phone}" : '')])
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                // ── Plan ─────────────────────────────────────────────────
                Select::make('subscription_plan_id')
                    ->label(__('Subscription Plan'))
                    ->options(fn () => SubscriptionPlan::query()->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn ($p) => [$p->id => "{$p->name} — " . number_format((float) $p->price, 2) . ' EGP'])
                        ->toArray()
                    )
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (blank($state)) {
                            $set('plan_price_display', null);
                            $set('custom_amount', null);
                            $set('ends_at', null);
                            return;
                        }
                        $plan = SubscriptionPlan::find($state);
                        if (! $plan) {
                            return;
                        }
                        $set('plan_price_display', number_format((float) $plan->price, 2));
                        $set('custom_amount', (float) $plan->price);
                        $set('ends_at', now()->addDays((int) $plan->duration_days)->toDateString());
                    }),

                // ── Plan price hint (read-only) ───────────────────────────
                TextInput::make('plan_price_display')
                    ->label(__('Plan Original Price'))
                    ->prefix('EGP')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('—')
                    ->visible(fn (Get $get) => filled($get('subscription_plan_id'))),

                // ── Amount the admin will charge ──────────────────────────
                TextInput::make('custom_amount')
                    ->label(__('Amount to Charge'))
                    ->prefix('EGP')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->helperText(__('Set to 0 for a free subscription.')),

                // ── Dates ─────────────────────────────────────────────────
                DatePicker::make('starts_at')
                    ->label(__('Starts At'))
                    ->required(),

                DatePicker::make('ends_at')
                    ->label(__('Ends At'))
                    ->required(),

                // ── Subscription status ───────────────────────────────────
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'active'    => __('Active'),
                        'expired'   => __('Expired'),
                        'cancelled' => __('Cancelled'),
                    ])
                    ->default('active')
                    ->required(),

                // ── Payment method ────────────────────────────────────────
                Select::make('payment_method')
                    ->label(__('Payment Method'))
                    ->options([
                        'cash'          => __('Cash'),
                        'credit_card'   => __('Credit card'),
                        'bank_transfer' => __('Bank transfer'),
                        'fawry'         => __('Fawry'),
                        'instapay'      => __('Instapay'),
                        'vodafone_cash' => __('Vodafone cash'),
                        'free'          => __('Free'),
                    ])
                    ->required(),

                // ── Payment status ────────────────────────────────────────
                Select::make('payment_status')
                    ->label(__('Payment Status'))
                    ->options([
                        'pending'   => __('Pending'),
                        'completed' => __('Completed'),
                    ])
                    ->default('completed')
                    ->required(),

                // ── Transaction reference ─────────────────────────────────
                TextInput::make('transaction_reference')
                    ->label(__('Transaction Reference')),

                // ── Notes ─────────────────────────────────────────────────
                Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
            ]);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);
        $originalPrice  = (float) $plan->price;
        $chargedAmount  = (float) $data['custom_amount'];
        $discountAmount = max($originalPrice - $chargedAmount, 0);

        DB::transaction(function () use ($data, $plan, $originalPrice, $chargedAmount, $discountAmount) {
            $subscription = Subscription::create([
                'user_id'              => $data['user_id'],
                'subscription_plan_id' => $plan->id,
                'starts_at'            => $data['starts_at'],
                'ends_at'              => $data['ends_at'],
                'status'               => $data['status'],
                'payment_method'       => $data['payment_method'],
                'payment_reference'    => $data['transaction_reference'] ?? null,
                'notes'                => $data['notes'] ?? null,
            ]);

            Payment::create([
                'user_id'               => $data['user_id'],
                'subscription_id'       => $subscription->id,
                'amount'                => $chargedAmount,
                'original_amount'       => $originalPrice,
                'discount_amount'       => $discountAmount,
                'payment_method'        => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'status'                => $data['payment_status'],
                'notes'                 => $data['notes'] ?? null,
            ]);
        });

        Notification::make()
            ->title(__('Subscription created successfully'))
            ->success()
            ->send();

        $this->redirect(SubscriptionResource::getUrl('index'));
    }

    public function getTitle(): string
    {
        return __('Create Subscription');
    }
}
