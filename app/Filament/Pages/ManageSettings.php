<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageAccess;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\Visit;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ManageSettings extends Page implements HasTable
{
    use AuthorizesPageAccess;
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.manage-settings';

    protected static string | UnitEnum | null $navigationGroup = null;

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function getNavigationLabel(): string
    {
        return __('Referral Settings');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Referral & Rewards');
    }

    protected static function getAccessPermissionName(): ?string
    {
        return 'settings.manage';
    }

    public function getTitle(): string | Htmlable
    {
        return __('Referral Settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'referral_enabled'     => filter_var(Setting::getValue('referral_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'visit_points_reward'  => (int) Setting::getValue('visit_points_reward', 10),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Referral Program'))
                    ->schema([
                        Toggle::make('referral_enabled')
                            ->label(__('Enable referral program'))
                            ->default(true),
                    ]),

                Section::make(__('Visit Rewards'))
                    ->description(__('Points granted to the user when a visit is approved by admin.'))
                    ->schema([
                        TextInput::make('visit_points_reward')
                            ->label(__('Points per approved visit'))
                            ->numeric()
                            ->minValue(0)
                            ->default(10)
                            ->suffix('pts'),
                    ]),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $rewardTypeOptions = [
            Referral::REWARD_NONE => __('None'),
            Referral::REWARD_POINTS => __('Points'),
            Referral::REWARD_FIXED_DISCOUNT => __('Fixed discount'),
            Referral::REWARD_PERCENT_DISCOUNT => __('Percent discount'),
        ];

        return $table
            ->query(SubscriptionPlan::query())
            ->heading(__('Rewards per Subscription Plan'))
            ->description(__('Set the referral rewards for each plan. Changes are saved immediately.'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Plan'))
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('egp')
                    ->sortable(),
                TextInputColumn::make('referrer_reward_points')
                    ->label(__('Referrer reward points'))
                    ->type('number')
                    ->rules(['min:0', 'integer']),
                SelectColumn::make('referee_reward_type')
                    ->label(__('New user reward type'))
                    ->options($rewardTypeOptions)
                    ->selectablePlaceholder(false)
                    ->default(Referral::REWARD_NONE),
                TextInputColumn::make('referee_reward_value')
                    ->label(__('New user reward value') . ' — ' . __('Points, fixed amount, or percent (0-100)'))
                    ->type('number')
                    ->rules(fn ($record) => $record?->referee_reward_type === Referral::REWARD_PERCENT_DISCOUNT
                        ? ['min:0', 'max:100', 'numeric']
                        : ['min:0', 'numeric']
                    ),
            ])
            ->paginated(false);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            Setting::setValue('referral_enabled', $data['referral_enabled'] ?? true);
            Setting::setValue('visit_points_reward', (int) ($data['visit_points_reward'] ?? 10));

            Notification::make()
                ->title(__('Settings saved successfully'))
                ->success()
                ->send();
        } catch (\Exception $exception) {
            Notification::make()
                ->title(__('Error saving settings'))
                ->danger()
                ->send();
        }
    }
}
