<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use Domain\Business\Models\Category;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('business.categories');
    }

    public static function getModelLabel(): string
    {
        return __('business.category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.categories');
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
                                    ->maxLength(255),
                                Select::make('parent_id')
                                    ->label(__('business.parent_category'))
                                    ->options(function () {
                                        return Category::where('parent_id', 0)
                                            ->orWhereNull('parent_id')
                                            ->where('status', 1)
                                            ->whereNotNull('title')
                                            ->where('title', '!=', '')
                                            ->pluck('title', 'id')
                                            ->prepend(__('business.root_category'), 0);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->default(0)
                                    ->helperText(__('business.select_parent_category_help')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('priority')
                                    ->label(__('business.priority'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->helperText(__('business.priority_help')),
                                Toggle::make('status')
                                    ->label(__('business.active'))
                                    ->default(true)
                                    ->helperText(__('business.status_help')),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('business.media_files'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('business.image'))
                            ->image()
                            ->disk('s3')
                            ->directory('categories/images')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull()
                            ->helperText(__('business.category_image_help')),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')
                    ->label(__('business.image'))
                    ->circular()
                    ->size(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')
                    ->label(__('business.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('parent.title')
                    ->label(__('business.parent_category'))
                    ->formatStateUsing(fn ($state) => $state ?: __('business.root_category'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('—'),
                TextColumn::make('children_count')
                    ->label(__('business.child_categories'))
                    ->counts('children')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default(0),
                TextColumn::make('businesses_count')
                    ->label(__('business.businesses'))
                    ->counts('businesses')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->default(0),
                BadgeColumn::make('status')
                    ->label(__('business.status'))
                    ->formatStateUsing(fn ($state) => $state ? __('business.active') : __('business.inactive'))
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ]),
                TextColumn::make('priority')
                    ->label(__('business.priority'))
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->searchable()
            ->filters([
                SelectFilter::make('parent_id')
                    ->label(__('business.parent_category'))
                    ->options(function () {
                        return Category::where('parent_id', 0)
                            ->orWhereNull('parent_id')
                            ->whereNotNull('title')
                            ->where('title', '!=', '')
                            ->pluck('title', 'id')
                            ->prepend(__('business.root_category'), 0);
                    }),
                TernaryFilter::make('status')
                    ->label(__('business.status'))
                    ->placeholder(__('business.all'))
                    ->trueLabel(__('business.active'))
                    ->falseLabel(__('business.inactive')),
                SelectFilter::make('user_id')
                    ->label(__('business.user'))
                    ->relationship('user', 'first_name', function (Builder $query) {
                        return $query->whereNotNull('first_name')->where('first_name', '!=', '');
                    }),
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
            RelationManagers\FiltersRelationManager::class,
            RelationManagers\FacilitiesRelationManager::class,
            RelationManagers\ServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure parent_id is set properly
        if (!isset($data['parent_id']) || $data['parent_id'] == 0) {
            $data['parent_id'] = 0;
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure parent_id is set properly
        if (!isset($data['parent_id']) || $data['parent_id'] == 0) {
            $data['parent_id'] = 0;
        }

        // Set default status
        if (!isset($data['status'])) {
            $data['status'] = 1;
        }

        // Set default priority
        if (!isset($data['priority'])) {
            $data['priority'] = 0;
        }

        return $data;
    }

    protected function mutateFormDataBeforeUpdate(array $data): array
    {
        // Ensure parent_id is set properly
        if (!isset($data['parent_id']) || $data['parent_id'] == 0) {
            $data['parent_id'] = 0;
        }

        return $data;
    }
}