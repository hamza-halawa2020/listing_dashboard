<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageAccess;
use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ManageContactSettings extends Page
{
    use AuthorizesPageAccess;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.manage-contact-settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Contact Settings');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Settings');
    }

    protected static function getAccessPermissionName(): ?string
    {
        return 'contact_settings.manage';
    }

    public function getTitle(): string|Htmlable
    {
        return __('Contact Settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'phone'     => Setting::getValue('phone'),
            'whatsapp'  => Setting::getValue('whatsapp'),
            'instapay'  => Setting::getValue('instapay'),
            'vodafonecash'  => Setting::getValue('vodafonecash'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Contact Information'))
                    ->description(__('These details appear on the website and are used for customer communication.'))
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->label(__('Phone Number'))
                            ->tel()
                            ->placeholder('+20 1xxxxxxxxx'),

                        TextInput::make('whatsapp')
                            ->label(__('WhatsApp Number'))
                            ->tel()
                            ->placeholder('+20 1xxxxxxxxx')
                            ->helperText(__('Include country code, e.g. +201234567890')),
                        TextInput::make('instapay')
                            ->label(__('Instapay Number'))
                            ->tel()
                            ->placeholder('+20 1xxxxxxxxx')
                            ->placeholder('+20 1xxxxxxxxx'),
                        TextInput::make('vodafonecash')
                            ->label(__('Vdafone Cash Number'))
                            ->tel()
                            ->placeholder('+20 1xxxxxxxxx')
                            ->placeholder('+20 1xxxxxxxxx'),
                    ]),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $keys = ['phone', 'whatsapp','instapay','vodafonecash'];

        foreach ($keys as $key) {
            Setting::setValue($key, $data[$key] ?? '');
        }

        Notification::make()
            ->title(__('Contact settings saved successfully'))
            ->success()
            ->send();
    }
}
