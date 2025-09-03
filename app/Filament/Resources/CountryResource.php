<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use Domain\Address\Models\Country;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TernaryFilter;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Address';

    public static function getNavigationGroup(): ?string
    {
        return __('site.Address Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('address.countries');
    }

    public static function getModelLabel(): string
    {
        return __('address.country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('address.countries');
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
                        Grid::make(2)
                            ->schema([
                                Toggle::make('status')
                                    ->label(__('address.active'))
                                    ->default(true),
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
                TextColumn::make('cities_count')
                    ->label(__('address.cities'))
                    ->counts('cities')
                    ->sortable()
                    ->badge()
                    ->color('success'),
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
                TernaryFilter::make('status')
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\CitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'view' => Pages\ViewCountry::route('/{record}'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        if (!isset($data['status'])) {
            $data['status'] = true;
        }

        if (!isset($data['priority'])) {
            $data['priority'] = 0;
        }

        return $data;
    }

    protected function mutateFormDataBeforeUpdate(array $data): array
    {
        // Ensure active is boolean
        if (isset($data['status'])) {
            $data['status'] = (bool) $data['status'];
        }

        return $data;
    }
}
