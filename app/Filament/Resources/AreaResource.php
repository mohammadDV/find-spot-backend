<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use Domain\Address\Models\Area;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Address';

    public static function getNavigationGroup(): ?string
    {
        return __('site.Address Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('address.areas');
    }

    public static function getModelLabel(): string
    {
        return __('address.area');
    }

    public static function getPluralModelLabel(): string
    {
        return __('address.areas');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('address.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('address.title'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Select::make('city_id')
                            ->label(__('address.city'))
                            ->relationship('city', 'title', function ($query) {
                                return $query->whereNotNull('title')->where('title', '!=', '');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('status')
                                    ->label(__('address.status'))
                                    ->default(1),
                                TextInput::make('priority')
                                    ->label(__('address.priority'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->helperText(__('address.priority_help')),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('address.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('address.area_code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('city.title')
                    ->label(__('address.city'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('city.country.title')
                    ->label(__('address.country'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                BadgeColumn::make('status')
                    ->label(__('address.status'))
                    ->formatStateUsing(fn ($state) => $state ? __('address.active') : __('address.inactive'))
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ]),
                TextColumn::make('priority')
                    ->label(__('address.priority'))
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('created_at')
                    ->label(__('address.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->searchable()
            ->filters([
                SelectFilter::make('city_id')
                    ->label(__('address.city'))
                    ->relationship('city', 'title', function (Builder $query) {
                        return $query->whereNotNull('title')->where('title', '!=', '');
                    }),
                SelectFilter::make('city.country_id')
                    ->label(__('address.country'))
                    ->relationship('city.country', 'title', function (Builder $query) {
                        return $query->whereNotNull('title')->where('title', '!=', '');
                    }),
                TernaryFilter::make('active')
                    ->label(__('address.status'))
                    ->placeholder(__('address.all'))
                    ->trueLabel(__('address.active'))
                    ->falseLabel(__('address.inactive')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('address.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('address.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('address.delete_selected')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'view' => Pages\ViewArea::route('/{record}'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        if (!isset($data['active'])) {
            $data['active'] = true;
        }

        if (!isset($data['priority'])) {
            $data['priority'] = 0;
        }

        return $data;
    }

    protected function mutateFormDataBeforeUpdate(array $data): array
    {
        // Ensure active is boolean
        if (isset($data['active'])) {
            $data['active'] = (bool) $data['active'];
        }

        return $data;
    }
}
