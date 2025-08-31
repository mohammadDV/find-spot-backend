<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Grid;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'دسته‌بندی‌ها';

    public static function getModelLabel(): string
    {
        return __('business.categories');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.categories');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('parent_category_id')
                            ->label(__('business.parent_category'))
                            ->options(function () {
                                return \Domain\Business\Models\Category::where('parent_id', 0)
                                    ->orWhereNull('parent_id')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder(__('business.select_parent_category'))
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('category_id', null);
                                }
                            })
                            ->helperText(__('business.select_parent_to_see_children')),
                        Select::make('category_id')
                            ->label(__('business.category'))
                            ->options(function ($get) {
                                $parentId = $get('parent_category_id');
                                if (!$parentId) {
                                    return [];
                                }

                                return \Domain\Business\Models\Category::where('parent_id', $parentId)
                                    ->whereNotNull('title')
                                    ->where('title', '!=', '')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder(__('business.select_child_categories'))
                            ->disabled(fn ($get) => !$get('parent_category_id'))
                            ->required()
                            ->helperText(function ($get) {
                                $parentId = $get('parent_category_id');
                                if (!$parentId) {
                                    return __('business.select_parent_first');
                                }

                                $childCount = \Domain\Business\Models\Category::where('parent_id', $parentId)
                                    ->count();

                                return __('business.available_child_categories', ['count' => $childCount]);
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('business.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.title')
                    ->label(__('business.parent_category'))
                    ->formatStateUsing(fn ($state) => $state ? $state : __('business.root_category'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('business.description'))
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->relationship('parent', 'title', function ($query) {
                        return $query->whereNotNull('title')->where('title', '!=', '')->select('id', 'title');
                    })
                    ->label(__('business.parent_category')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordTitleAttribute('title')
                    ->form([
                        Select::make('parent_category_id')
                            ->label(__('business.parent_category'))
                            ->options(function () {
                                return \Domain\Business\Models\Category::where('parent_id', 0)
                                    ->orWhereNull('parent_id')
                                    ->whereNotNull('title')
                                    ->where('title', '!=', '')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder(__('business.select_parent_category'))
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('category_id', null);
                                }
                            })
                            ->helperText(__('business.select_parent_to_see_children')),
                        Select::make('category_id')
                            ->label(__('business.category'))
                            ->options(function ($get) {
                                $parentId = $get('parent_category_id');
                                if (!$parentId) {
                                    return [];
                                }

                                return \Domain\Business\Models\Category::where('parent_id', $parentId)
                                    ->whereNotNull('title')
                                    ->where('title', '!=', '')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder(__('business.select_child_categories'))
                            ->disabled(fn ($get) => !$get('parent_category_id'))
                            ->required()
                            ->helperText(function ($get) {
                                $parentId = $get('parent_category_id');
                                if (!$parentId) {
                                    return __('business.select_parent_first');
                                }

                                $childCount = \Domain\Business\Models\Category::where('parent_id', $parentId)
                                    ->count();

                                return __('business.available_child_categories', ['count' => $childCount]);
                            }),
                    ])
                    ->action(function (array $data) {
                        // Get the parent record (business)
                        $business = $this->getOwnerRecord();

                        // Get the category to attach
                        $category = \Domain\Business\Models\Category::find($data['category_id']);

                        if ($category && $business) {
                            // Attach the category to the business
                            $business->categories()->attach($category->id);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
