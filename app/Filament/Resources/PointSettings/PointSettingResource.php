<?php

namespace App\Filament\Resources\PointSettings;

use App\Filament\Resources\PointSettings\Pages\ManagePointSettings;
use App\Models\PointSetting;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Actions\EditAction;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;



class PointSettingResource extends Resource
{
    protected static ?string $model = PointSetting::class;

    // protected static ?string $navigationIcon = 'heroicon-o-coins';
    // protected static string | \UnitEnum | null $navigationGroup = 'System Settings';
    // protected static ?string $navigationIcon = 'heroicon-o-coins';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;


    
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false; // Only one record allowed
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Point Rate Settings')
                    ->description('Configure the conversion rate between points and Egyptian Pounds')
                    ->schema([
                        TextInput::make('points_to_egp_rate')
                            ->label('Point Value (EGP)')
                            ->helperText('How much one point is worth in Egyptian Pounds (e.g., 0.1000 = 10 piasters)')
                            ->required()
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0.0001)
                            ->maxValue(9999.9999)
                            ->prefix('EGP')
                            ->suffix('per point')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Calculate examples
                                $rate = (float) $state;
                                $set('example_100_egp', number_format(100 / $rate, 0) . ' points');
                                $set('example_1000_egp', number_format(1000 / $rate, 0) . ' points');
                                $set('example_100_points', number_format(100 * $rate, 2) . ' EGP');
                                $set('example_1000_points', number_format(1000 * $rate, 2) . ' EGP');
                            }),

                        Forms\Components\Placeholder::make('example_100_egp')
                            ->label('100 EGP = ? points')
                            ->content('1000 points'),

                        Forms\Components\Placeholder::make('example_1000_egp')
                            ->label('1000 EGP = ? points')
                            ->content('10000 points'),

                        Forms\Components\Placeholder::make('example_100_points')
                            ->label('100 points = ? EGP')
                            ->content('10.00 EGP'),

                        Forms\Components\Placeholder::make('example_1000_points')
                            ->label('1000 points = ? EGP')
                            ->content('100.00 EGP'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->helperText('Any additional notes about this point rate setting')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\Hidden::make('reason')
                            ->default('Updated from admin panel'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('points_to_egp_rate')
                    ->label('Point Value')
                    ->money('EGP')
                    ->formatStateUsing(fn ($state) => number_format($state, 4) . ' EGP/point')
                    ->sortable(),

                Tables\Columns\TextColumn::make('points_to_egp_rate')
                    ->label('Points per 100 EGP')
                    ->formatStateUsing(fn ($state) => number_format(100 / $state, 0) . ' points')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Record the change in history
                        $current = PointSetting::first();
                        $oldRate = $current?->points_to_egp_rate ?? 0.1000;
                        $newRate = (float) $data['points_to_egp_rate'];

                        if (abs($oldRate - $newRate) >= 0.0001) {
                            \App\Models\PointRateHistory::create([
                                'old_rate' => $oldRate,
                                'new_rate' => $newRate,
                                'reason' => $data['reason'] ?? 'Updated from admin panel',
                                'changed_by_admin_id' => auth()->id(),
                            ]);
                        }

                        return $data;
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePointSettings::route('/'),
            'history' => Pages\ViewPointRateHistory::route('/history'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest();
    }
}
