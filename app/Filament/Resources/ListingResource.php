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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        // Level 1 - Root
                        Select::make('location_level_1')
                            ->label('Country')
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
                            ->label('Governorate')
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
                            ->label('Area')
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
                            ->label('Sub Area')
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
                            ->label('Sub Sub Area')
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
                            ->label('Selected Location')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->formatStateUsing(fn ($state) => $state ? Location::find($state)?->name . ' (ID: ' . $state . ')' : null)
                            ->helperText('This will be auto-filled based on your location selection'),
                        
                        TextInput::make('address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
                
                Section::make('Location on Map')
                    ->schema([
                        MapPicker::make('map_location')
                            ->label('Select Location on Map')
                            ->defaultLocation(30.0444, 31.2357)
                            ->defaultZoom(6)
                            ->columnSpanFull()
                            ->helperText('Click on the map or drag the marker to select the exact location'),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('30.0444')
                            ->helperText('Auto-filled from map or enter manually'),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('31.2357')
                            ->helperText('Auto-filled from map or enter manually'),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make('Phone Numbers')
                    ->schema([
                        Repeater::make('phones')
                            ->relationship()
                            ->schema([
                                TextInput::make('phone_number')
                                    ->label('Phone Number')
                                    ->required()
                                    ->tel()
                                    ->maxLength(255),
                                Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'landline' => 'Landline',
                                        'mobile' => 'Mobile',
                                        'whatsapp' => 'WhatsApp',
                                    ])
                                    ->required()
                                    ->default('mobile'),
                                TextInput::make('contact_person')
                                    ->label('Contact Person')
                                    ->maxLength(255)
                                    ->placeholder('Optional - e.g., Dr. Ahmed, Reception')
                                    ->helperText('Optional: Name of the person responsible for this number'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add Phone Number')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['phone_number']) 
                                    ? $state['phone_number'] . (isset($state['contact_person']) && $state['contact_person'] ? ' - ' . $state['contact_person'] : '')
                                    : null
                            ),
                    ])
                    ->collapsible(),

                Section::make('Images')
                    ->schema([
                        Repeater::make('images')
                            ->relationship()
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Image')
                                    ->image()
                                    ->directory('listings')
                                    ->required()
                                    ->columnSpanFull(),
                                Toggle::make('is_cover')
                                    ->label('Set as Cover Image')
                                    ->default(false),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Image')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['is_cover'] ? '⭐ Cover Image' : 'Image'),
                    ])
                    ->collapsible(),
                    
                Section::make('Offers')
                    ->schema([
                        Repeater::make('offers')
                            ->relationship()
                            ->schema([
                                TextInput::make('title')
                                    ->label('Offer Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                TextInput::make('discount_percentage')
                                    ->label('Discount %')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add Offer')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Offer'),
                    ])
                    ->collapsible(),

                
                Section::make('Working Hours')
                    ->schema([
                        Repeater::make('workingHours')
                            ->relationship()
                            ->schema([
                                Select::make('day')
                                    ->label('Day')
                                    ->options([
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday',
                                        'monday' => 'Monday',
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->distinct()
                                    ->columnSpanFull(),
                                Toggle::make('is_closed')
                                    ->label('Closed')
                                    ->default(false)
                                    ->live()
                                    ->columnSpanFull(),
                                TextInput::make('open_time')
                                    ->label('Opening Time')
                                    ->type('time')
                                    ->required(fn (Get $get): bool => !$get('is_closed'))
                                    ->hidden(fn (Get $get): bool => $get('is_closed')),
                                TextInput::make('close_time')
                                    ->label('Closing Time')
                                    ->type('time')
                                    ->required(fn (Get $get): bool => !$get('is_closed'))
                                    ->hidden(fn (Get $get): bool => $get('is_closed')),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add Working Day')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['day']) 
                                    ? ($state['day'] ?? '') . ($state['is_closed'] ?? false ? ' - Closed' : ' (' . ($state['open_time'] ?? '') . ' - ' . ($state['close_time'] ?? '') . ')')
                                    : 'Working Day'
                            )
                            ->helperText('Add working hours for each day. Leave empty for days you are closed.'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('location.name')->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
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
