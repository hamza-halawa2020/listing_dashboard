<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Filament\Resources\ListingResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ListingResource\RelationManagers\OffersRelationManager;
use App\Filament\Resources\ListingResource\RelationManagers\PhonesRelationManager;
use App\Forms\Components\MapPicker;
use App\Models\Listing;
use App\Models\Location;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    public static function getModelLabel(): string
    {
        return __('Listing');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Listings');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Basic Information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label(__('Category'))
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        // Level 1 - Root
                        Select::make('location_level_1')
                            ->label(__('Country'))
                            ->options(Location::whereNull('parent_id')->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('location_level_2', null);
                                $set('location_level_3', null);
                                $set('location_level_4', null);
                                $set('location_level_5', null);
                                // Set final location_id
                                $hasChildren = Location::where('parent_id', $state)->exists();
                                if (!$hasChildren) {
                                    $set('location_id', $state);
                                } else {
                                    $set('location_id', null);
                                }
                            })
                            ->columnSpanFull(),
                        
                        // Level 2 - Governorate
                        Select::make('location_level_2')
                            ->label(__('Governorate'))
                            ->options(fn (Get $get): array => 
                                $get('location_level_1') 
                                    ? Location::where('parent_id', $get('location_level_1'))->pluck('name', 'id')->toArray()
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('location_level_3', null);
                                $set('location_level_4', null);
                                $set('location_level_5', null);
                                // Set final location_id
                                $hasChildren = Location::where('parent_id', $state)->exists();
                                if (!$hasChildren) {
                                    $set('location_id', $state);
                                } else {
                                    $set('location_id', null);
                                }
                            })
                            ->visible(fn (Get $get): bool => 
                                filled($get('location_level_1')) && 
                                Location::where('parent_id', $get('location_level_1'))->exists()
                            ),
                        
                        Select::make('location_level_3')
                            ->label(__('Area'))
                            ->options(fn (Get $get): array => 
                                $get('location_level_2') 
                                    ? Location::where('parent_id', $get('location_level_2'))->pluck('name', 'id')->toArray()
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('location_level_4', null);
                                $set('location_level_5', null);
                            })
                            ->visible(fn (Get $get): bool => 
                                filled($get('location_level_2')) && 
                                Location::where('parent_id', $get('location_level_2'))->exists()
                            )
                            ->required(fn (Get $get): bool => 
                                filled($get('location_level_2')) && 
                                Location::where('parent_id', $get('location_level_2'))->exists() &&
                                !Location::where('parent_id', $get('location_level_2'))->exists()
                            ),
                        
                        // Level 4 - Sub Area
                        Select::make('location_level_4')
                            ->label(__('Sub Area'))
                            ->options(fn (Get $get): array => 
                                $get('location_level_3') 
                                    ? Location::where('parent_id', $get('location_level_3'))->pluck('name', 'id')->toArray()
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->afterStateHydrated(function (callable $set, $state) {
                                // Set location_id when form loads (for edit mode)
                                if ($state) {
                                    $hasChildren = Location::where('parent_id', $state)->exists();
                                    if (!$hasChildren) {
                                        $set('location_id', $state);
                                    }
                                }
                            })
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('location_level_5', null);
                                // Set final location_id
                                $hasChildren = Location::where('parent_id', $state)->exists();
                                if (!$hasChildren) {
                                    $set('location_id', $state);
                                } else {
                                    $set('location_id', null);
                                }
                            })
                            ->visible(fn (Get $get): bool => 
                                filled($get('location_level_3')) && 
                                Location::where('parent_id', $get('location_level_3'))->exists()
                            ),
                        
                        // Level 5 - Sub Sub Area
                        Select::make('location_level_5')
                            ->label(__('Sub Sub Area'))
                            ->options(fn (Get $get): array => 
                                $get('location_level_4') 
                                    ? Location::where('parent_id', $get('location_level_4'))->pluck('name', 'id')->toArray()
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->afterStateHydrated(function (callable $set, $state) {
                                // Set location_id when form loads (for edit mode)
                                if ($state) {
                                    $set('location_id', $state);
                                }
                            })
                            ->afterStateUpdated(function (callable $set, $state) {
                                // Set final location_id
                                $set('location_id', $state);
                            })
                            ->visible(fn (Get $get): bool => 
                                filled($get('location_level_4')) && 
                                Location::where('parent_id', $get('location_level_4'))->exists()
                            ),
                        
                        // Hidden field to store the final location_id
                        TextInput::make('location_id')
                            ->label(__('Selected Location'))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->formatStateUsing(fn ($state) => $state ? Location::find($state)?->name . ' (ID: ' . $state . ')' : null)
                            ->helperText(__('This will be auto-filled based on your location selection')),
                        
                        TextInput::make('address')
                            ->label(__('Address'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                    ])
                    ->columns(2),
                
                Section::make(__('Location on Map'))
                    ->schema([
                        MapPicker::make('map_location')
                            ->label(__('Select Location on Map'))
                            ->defaultLocation(30.0444, 31.2357)
                            ->defaultZoom(6)
                            ->columnSpanFull()
                            ->helperText(__('Click on the map or drag the marker to select the exact location')),
                        TextInput::make('latitude')
                            ->label(__('Latitude'))
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('30.0444')
                            ->helperText(__('Auto-filled from map or enter manually')),
                        TextInput::make('longitude')
                            ->label(__('Longitude'))
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('31.2357')
                            ->helperText(__('Auto-filled from map or enter manually')),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make(__('Phone Numbers'))
                    ->schema([
                        Repeater::make('phones')
                            ->relationship()
                            ->schema([
                                TextInput::make('phone_number')
                                    ->label(__('Phone Number'))
                                    ->required()
                                    ->tel()
                                    ->maxLength(255),
                                Select::make('type')
                                    ->label(__('Type'))
                                    ->options([
                                        'landline' => __('Landline'),
                                        'mobile' => __('Mobile'),
                                        'whatsapp' => __('WhatsApp'),
                                    ])
                                    ->required()
                                    ->default('mobile'),
                                TextInput::make('contact_person')
                                    ->label(__('Contact Person'))
                                    ->maxLength(255)
                                    ->placeholder(__('Optional - e.g., Dr. Ahmed, Reception'))
                                    ->helperText(__('Optional: Name of the person responsible for this number')),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel(__('Add Phone Number'))
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['phone_number']) 
                                    ? $state['phone_number'] . (isset($state['contact_person']) && $state['contact_person'] ? ' - ' . $state['contact_person'] : '')
                                    : null
                            ),
                    ])
                    ->collapsible(),

                Section::make(__('Images'))
                    ->schema([
                        Repeater::make('images')
                            ->relationship()
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label(__('Image'))
                                    ->image()
                                    // ->directory('listings')
                                    ->required()
                                    ->columnSpanFull(),
                                Toggle::make('is_cover')
                                    ->label(__('Set as Cover Image'))
                                    ->default(false),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel(__('Add Image'))
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['is_cover'] ? __('Cover Image') : __('Image')),
                    ])
                    ->collapsible(),
                    
                Section::make(__('Offers'))
                    ->schema([
                        Repeater::make('offers')
                            ->relationship()
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('Offer Title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Textarea::make('description')
                                    ->label(__('Description'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                                TextInput::make('discount_percentage')
                                    ->label(__('Discount %'))
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel(__('Add Offer'))
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? __('Offer')),
                    ])
                    ->collapsible(),

                
                Section::make(__('Working Hours'))
                    ->schema([
                        Repeater::make('workingHours')
                            ->relationship()
                            ->schema([
                                Select::make('day')
                                    ->label(__('Day'))
                                    ->options([
                                        'saturday' => __('Saturday'),
                                        'sunday' => __('Sunday'),
                                        'monday' => __('Monday'),
                                        'tuesday' => __('Tuesday'),
                                        'wednesday' => __('Wednesday'),
                                        'thursday' => __('Thursday'),
                                        'friday' => __('Friday'),
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->distinct()
                                    ->columnSpanFull(),
                                Toggle::make('is_closed')
                                    ->label(__('Closed'))
                                    ->default(false)
                                    ->live()
                                    ->columnSpanFull(),
                                TextInput::make('open_time')
                                    ->label(__('Opening Time'))
                                    ->type('time')
                                    ->required(fn (Get $get): bool => !$get('is_closed'))
                                    ->hidden(fn (Get $get): bool => $get('is_closed')),
                                TextInput::make('close_time')
                                    ->label(__('Closing Time'))
                                    ->type('time')
                                    ->required(fn (Get $get): bool => !$get('is_closed'))
                                    ->hidden(fn (Get $get): bool => $get('is_closed')),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel(__('Add Working Day'))
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['day']) 
                                    ? ($state['day'] ?? '') . ($state['is_closed'] ?? false ? ' - ' . __('Closed') : ' (' . ($state['open_time'] ?? '') . ' - ' . ($state['close_time'] ?? '') . ')')
                                    : __('Working Day')
                            )
                            ->helperText(__('Add working hours for each day. Leave empty for days you are closed.')),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable(),
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('category.name')->label(__('Category'))->sortable(),
                TextColumn::make('location.name')->label(__('Location'))->sortable(),
                IconColumn::make('is_active')->label(__('Active'))->boolean(),
                TextColumn::make('created_at')->label(__('Created At'))->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label(__('Active')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            PhonesRelationManager::class,
            OffersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }
}
