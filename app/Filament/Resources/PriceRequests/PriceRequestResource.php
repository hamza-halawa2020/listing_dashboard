<?php

namespace App\Filament\Resources\PriceRequests;

use App\Filament\Resources\AuthorizedResource;
use App\Filament\Resources\PriceRequests\Pages\ManagePriceRequests;
use App\Mail\PriceRequestPdfMail;
use App\Models\PriceRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Throwable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class PriceRequestResource extends AuthorizedResource
{
    protected static ?string $model = PriceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'contact_person';

    public static function getModelLabel(): string
    {
        return __('Price Request');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Price Requests');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['createdBy', 'respondedBy']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label(__('Company Name'))
                    ->nullable(),
                TextInput::make('contact_person')
                    ->label(__('Contact Person'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel()
                    ->required(),
                Select::make('company_type')
                    ->label(__('Company Type'))
                    ->options([
                        'individual' => __('Individual'),
                        'company' => __('Company'),
                        'organization' => __('Organization'),
                    ])
                    ->required(),
                TextInput::make('employee_count')
                    ->label(__('Employee Count'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10000)
                    ->nullable(),
                Textarea::make('services_needed')
                    ->label(__('Services Needed'))
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('additional_requirements')
                    ->label(__('Additional Requirements'))
                    ->nullable()
                    ->columnSpanFull(),
                Select::make('budget_range')
                    ->label(__('Budget Range'))
                    ->options([
                        'under_1000' => __('Under 1000 EGP'),
                        '1000_5000' => __('1000 - 5000 EGP'),
                        '5000_10000' => __('5000 - 10000 EGP'),
                        '10000_25000' => __('10000 - 25000 EGP'),
                        'over_25000' => __('Over 25000 EGP'),
                    ])
                    ->nullable(),
                Select::make('timeline')
                    ->label(__('Timeline'))
                    ->options([
                        'urgent' => __('Urgent (1 week)'),
                        'week' => __('1 Week'),
                        'month' => __('1 Month'),
                        'quarter' => __('3 Months'),
                        'flexible' => __('Flexible'),
                    ])
                    ->nullable(),
                Toggle::make('status')
                    ->label(__('Responded'))
                    ->required(),
                Textarea::make('response_notes')
                    ->label(__('Response Notes'))
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company_name')
                    ->label(__('Company Name'))
                    ->placeholder('-'),
                TextEntry::make('contact_person')
                    ->label(__('Contact Person'))
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label(__('Email'))
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->label(__('Phone'))
                    ->placeholder('-'),
                TextEntry::make('company_type_label')
                    ->label(__('Company Type'))
                    ->placeholder('-'),
                TextEntry::make('employee_count')
                    ->label(__('Employee Count'))
                    ->placeholder('-'),
                TextEntry::make('services_needed')
                    ->label(__('Services Needed'))
                    ->columnSpanFull(),
                TextEntry::make('additional_requirements')
                    ->label(__('Additional Requirements'))
                    ->columnSpanFull()
                    ->placeholder('-'),
                TextEntry::make('budget_range_label')
                    ->label(__('Budget Range'))
                    ->placeholder('-'),
                TextEntry::make('timeline_label')
                    ->label(__('Timeline'))
                    ->placeholder('-'),
                IconEntry::make('status')
                    ->label(__('Responded'))
                    ->boolean(),
                TextEntry::make('respondedBy.name')
                    ->label(__('Responded By'))
                    ->placeholder('-'),
                TextEntry::make('responded_at')
                    ->label(__('Responded At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('response_notes')
                    ->label(__('Response Notes'))
                    ->columnSpanFull()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('contact_person')
            ->columns([
                TextColumn::make('company_name')
                    ->label(__('Company'))
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('contact_person')
                    ->label(__('Contact Person'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone')),
                TextColumn::make('company_type_label')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'فرد' => 'success',
                        'شركة' => 'primary',
                        'منظمة' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('employee_count')
                    ->label(__('Employees'))
                    ->numeric()
                    ->placeholder('-'),
                TextColumn::make('budget_range_label')
                    ->label(__('Budget'))
                    ->placeholder('-'),
                TextColumn::make('timeline_label')
                    ->label(__('Timeline'))
                    ->placeholder('-'),
                ToggleColumn::make('status')
                    ->label(__('Responded'))
                    ->sortable()
                    ->afterStateUpdated(function (PriceRequest $record, bool $state): void {
                        if ($state) {
                            $record->responded_by = auth()->id();
                            $record->responded_at = now();
                        } else {
                            $record->responded_by = null;
                            $record->responded_at = null;
                        }
                        $record->save();
                    }),
                TextColumn::make('respondedBy.name')
                    ->label(__('Responded By'))
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('pending')
                    ->query(fn (Builder $query): Builder => $query->where('status', false))
                    ->toggle(),
                Filter::make('responded')
                    ->query(fn (Builder $query): Builder => $query->where('status', true))
                    ->toggle(),
                Filter::make('companies')
                    ->query(fn (Builder $query): Builder => $query->where('company_type', 'company'))
                    ->toggle(),
                Filter::make('individuals')
                    ->query(fn (Builder $query): Builder => $query->where('company_type', 'individual'))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('download_pdf')
                    ->label(__('Download PDF'))
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn (PriceRequest $record): string => route('price-requests.pdf.download', $record))
                    ->openUrlInNewTab(),
                Action::make('send_pdf_email')
                    ->label(__('Send PDF by Email'))
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->form([
                        TextInput::make('recipient_email')
                            ->label(__('Recipient Email'))
                            ->email()
                            ->required()
                            ->default(fn (PriceRequest $record): string => $record->email),
                    ])
                    ->action(function (PriceRequest $record, array $data): void {
                        $recipient = (string) ($data['recipient_email'] ?? '');

                        Log::info('Price request PDF email attempt started', [
                            'price_request_id' => $record->id,
                            'recipient_email' => $recipient,
                            'mailer' => config('mail.default'),
                            'mail_host' => config('mail.mailers.smtp.host'),
                            'mail_port' => config('mail.mailers.smtp.port'),
                            'mail_encryption' => config('mail.mailers.smtp.scheme') ?? env('MAIL_ENCRYPTION'),
                            'mail_from' => config('mail.from.address'),
                            'queue_connection' => config('queue.default'),
                        ]);

                        try {
                            Mail::to($recipient)->send(new PriceRequestPdfMail($record));

                            Log::info('Price request PDF email sent successfully', [
                                'price_request_id' => $record->id,
                                'recipient_email' => $recipient,
                            ]);

                            Notification::make()
                                ->title(__('Email sent successfully'))
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Log::error('Price request PDF email failed', [
                                'price_request_id' => $record->id,
                                'recipient_email' => $recipient,
                                'error' => $e->getMessage(),
                                'exception_class' => $e::class,
                                'exception_file' => $e->getFile(),
                                'exception_line' => $e->getLine(),
                                'trace' => $e->getTraceAsString(),
                            ]);

                            Notification::make()
                                ->title(__('Failed to send email'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('share_link')
                    ->label(__('Share Link'))
                    ->icon(Heroicon::OutlinedLink)
                    ->fillForm(fn (PriceRequest $record): array => [
                        'share_link' => URL::temporarySignedRoute(
                            'price-requests.share',
                            now()->addDays(30),
                            ['priceRequest' => $record->id],
                        ),
                    ])
                    ->form([
                        TextInput::make('share_link')
                            ->label(__('Share Link (valid for 30 days)'))
                            ->readOnly()
                            ->dehydrated(false),
                    ])
                    ->modalSubmitAction(false),
                ViewAction::make()
                    ->visible(fn ($record): bool => static::canView($record)),
                EditAction::make()
                    ->visible(fn ($record): bool => static::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn ($record): bool => static::canDelete($record)),
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
            'index' => ManagePriceRequests::route('/'),
        ];
    }
}
