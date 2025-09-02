<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterResource\Pages;
use App\Filament\Resources\FilterResource\RelationManagers;
use Domain\Business\Models\Filter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TernaryFilter;

class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('business.filters');
    }

    public static function getModelLabel(): string
    {
        return __('business.filter');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.filters');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('business.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('business.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('status')
                                    ->label(__('business.active'))
                                    ->default(true)
                                    ->helperText(__('business.status_help')),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('business.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('categories_count')
                    ->label(__('business.categories'))
                    ->counts('categories')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default(0),
                TextColumn::make('businesses_count')
                    ->label(__('business.businesses'))
                    ->counts('businesses')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->default(0),
                BadgeColumn::make('status')
                    ->label(__('business.status'))
                    ->formatStateUsing(fn ($state) => $state ? __('business.active') : __('business.inactive'))
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ]),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->searchable()
            ->filters([
                TernaryFilter::make('status')
                    ->label(__('business.status'))
                    ->placeholder(__('business.all'))
                    ->trueLabel(__('business.active'))
                    ->falseLabel(__('business.inactive')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('business.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('business.delete')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CategoriesRelationManager::class,
            RelationManagers\BusinessesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFilters::route('/'),
            'create' => Pages\CreateFilter::route('/create'),
            'view' => Pages\ViewFilter::route('/{record}'),
            'edit' => Pages\EditFilter::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default status
        if (!isset($data['status'])) {
            $data['status'] = 1;
        }

        return $data;
    }
}