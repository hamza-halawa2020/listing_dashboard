<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageAccess;
use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ManageSettings extends Page
{
    use AuthorizesPageAccess;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.manage-settings';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static string | UnitEnum | null $navigationGroup = null;

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Settings');
    }

    protected static function getAccessPermissionName(): ?string
    {
        return 'settings.manage';
    }

    public function getTitle(): string | Htmlable
    {
        return __('Settings');
    }

    public function mount(): void
    {
        $this->form->fill(Setting::getAllSettings());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Contact Information'))
                    ->schema([
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel(),
                        TextInput::make('whatsapp')
                            ->label(__('WhatsApp'))
                            ->tel(),
                        TextInput::make('facebook')
                            ->label(__('Facebook'))
                            ->url(),
                        TextInput::make('instagram')
                            ->label(__('Instagram'))
                            ->url(),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email(),
                    ])->columns(2),
                Section::make(__('About Us'))
                    ->schema([
                        Textarea::make('about_us')
                            ->label(__('About Us'))
                            ->rows(5),
                        Textarea::make('about_us_footer')
                            ->label(__('About Us (Footer)'))
                            ->rows(3),
                        TextInput::make('address')
                            ->label(__('Address')),
                    ]),
                Section::make(__('Policies'))
                    ->schema([
                        RichEditor::make('privacy_policy')
                            ->label(__('Privacy Policy'))
                            ->columnSpanFull(),
                        RichEditor::make('terms_conditions')
                            ->label(__('Terms & Conditions'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Media'))
                    ->schema([
                        FileUpload::make('logo')
                            ->label(__('Logo'))
                            ->directory('settings')
                            ->image(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            foreach ($data as $key => $value) {
                Setting::setValue($key, $value);
            }

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
