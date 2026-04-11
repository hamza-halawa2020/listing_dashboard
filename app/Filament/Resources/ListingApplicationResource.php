<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingApplicationResource\Pages\ListApplications;
use App\Filament\Resources\ListingApplicationResource\Pages\ViewApplication;
use App\Models\ListingApplication;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section as InfoSection;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Schemas\Schema;
use Filament\Infolists\Infolist as InfolistComponent;

class ListingApplicationResource extends AuthorizedResource
{
    protected static ?string $model = ListingApplication::class;

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;


    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('Listing Application');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Listing Applications');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ─── Application / Contact Info ───
                InfoSection::make(__('Application Information'))
                    ->schema([
                        TextEntry::make('id')
                            ->label(__('ID')),
                        TextEntry::make('contact_name')
                            ->label(__('Contact Person Name')),
                        TextEntry::make('contact_email')
                            ->label(__('Contact Email')),
                        TextEntry::make('contact_phone')
                            ->label(__('Contact Phone')),
                    ])
                    ->columns(2),

                // ─── Listing Basic Info ───
                InfoSection::make(__('Listing Information'))
                    ->schema([
                        TextEntry::make('listing.name')
                            ->label(__('Business Name')),
                        TextEntry::make('listing.category.name')
                            ->label(__('Category')),
                        TextEntry::make('listing.location.name')
                            ->label(__('Location')),
                        TextEntry::make('listing.address')
                            ->label(__('Address')),
                        TextEntry::make('listing.description')
                            ->label(__('Description'))
                            ->columnSpanFull(),
                        TextEntry::make('listing.latitude')
                            ->label(__('Latitude')),
                        TextEntry::make('listing.longitude')
                            ->label(__('Longitude')),
                        IconEntry::make('listing.is_active')
                            ->label(__('Active'))
                            ->boolean(),
                    ])
                    ->columns(2),

                // ─── Phone Numbers ───
                InfoSection::make(__('Phone Numbers'))
                    ->schema([
                        RepeatableEntry::make('listing.phones')
                            ->label('')
                            ->schema([
                                TextEntry::make('phone_number')
                                    ->label(__('Phone Number')),
                                TextEntry::make('type')
                                    ->label(__('Type'))
                                    ->badge(),
                                TextEntry::make('contact_person')
                                    ->label(__('Contact Person')),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible(),

                // ─── Working Hours ───
                InfoSection::make(__('Working Hours'))
                    ->schema([
                        RepeatableEntry::make('listing.workingHours')
                            ->label('')
                            ->schema([
                                TextEntry::make('day')
                                    ->label(__('Day'))
                                    ->badge(),
                                IconEntry::make('is_closed')
                                    ->label(__('Closed'))
                                    ->boolean()
                                    ->trueColor('danger')
                                    ->falseColor('success'),
                                TextEntry::make('open_time')
                                    ->label(__('Opening Time')),
                                TextEntry::make('close_time')
                                    ->label(__('Closing Time')),
                            ])
                            ->columns(4),
                    ])
                    ->collapsible(),

                // ─── Links ───
                InfoSection::make(__('Links'))
                    ->schema([
                        RepeatableEntry::make('listing.links')
                            ->label('')
                            ->schema([
                                TextEntry::make('title')
                                    ->label(__('Title')),
                                TextEntry::make('url')
                                    ->label(__('URL'))
                                    ->url(fn ($state) => $state, true),
                            ])
                            ->columns(2),
                    ])
                    ->collapsible(),

                // ─── Images ───
                InfoSection::make(__('Images'))
                    ->schema([
                        RepeatableEntry::make('listing.images')
                            ->label('')
                            ->schema([
                                ImageEntry::make('image_path')
                                    ->label(__('Image'))
                                    ->disk('public')
                                    ->height(200)
                                    ->width(300),
                                IconEntry::make('is_cover')
                                    ->label(__('Cover Image'))
                                    ->boolean(),
                            ])
                            ->columns(2),
                    ])
                    ->collapsible(),

                // ─── Review Status ───
                InfoSection::make(__('Review Information'))
                    ->schema([
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('rejection_reason')
                            ->label(__('Rejection Reason')),
                        TextEntry::make('reviewer.name')
                            ->label(__('Reviewed By')),
                        TextEntry::make('reviewed_at')
                            ->label(__('Reviewed At'))
                            ->dateTime(),
                        TextEntry::make('admin_notes')
                            ->label(__('Admin Notes')),
                    ])
                    ->columns(2),

                // ─── Timestamps ───
                InfoSection::make(__('Timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('Created At'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('Updated At'))
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable(),
                TextColumn::make('listing.name')
                    ->label(__('Business Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_name')
                    ->label(__('Contact Person'))
                    ->searchable(),
                TextColumn::make('contact_email')
                    ->label(__('Email'))
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Submitted At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ListingApplication $record) => ViewApplication::getUrl([$record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'view' => ViewApplication::route('/{record}'),
        ];
    }
}

