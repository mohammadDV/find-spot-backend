<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class FiltersRelationManager extends RelationManager
{
    protected static string $relationship = 'filters';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'فیلترها';

    public static function getModelLabel(): string
    {
        return __('business.filter');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.filters');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('business.title'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('priority')
                            ->label(__('business.priority'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100),
                    ]),
                TextInput::make('description')
                    ->label(__('business.description'))
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Toggle::make('status')
                    ->label(__('business.status'))
                    ->default(true),
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
                TextColumn::make('description')
                    ->label(__('business.description'))
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('priority')
                    ->label(__('business.priority'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('business.status'))
                    ->formatStateUsing(fn ($state) => $state ? __('business.active') : __('business.inactive'))
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('business.filter_by_status'))
                    ->options([
                        1 => __('business.active'),
                        0 => __('business.inactive'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('business.attach_filter'))
                    ->form([
                        Select::make('filter_id')
                            ->label(__('business.select_filter'))
                            ->options(function () {
                                // Get the business record
                                $business = $this->getOwnerRecord();

                                if (!$business) {
                                    return [];
                                }

                                // Get filters that are related to the business's categories
                                $businessCategoryIds = $business->categories()->pluck('categories.id')->toArray();

                                if (empty($businessCategoryIds)) {
                                    return [];
                                }

                                // Get filters through category_filter table
                                return \Domain\Business\Models\Filter::where('status', 1)
                                    ->whereHas('categories', function ($query) use ($businessCategoryIds) {
                                        $query->whereIn('categories.id', $businessCategoryIds);
                                    })
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder(__('business.select_filter_to_attach'))
                            ->helperText(__('business.only_filters_for_business_categories')),
                    ])
                    ->action(function (array $data) {
                        // Get the parent record (business)
                        $business = $this->getOwnerRecord();

                        // Get the filter to attach
                        $filter = \Domain\Business\Models\Filter::find($data['filter_id']);

                        if ($filter && $business) {
                            // Attach the filter to the business
                            $business->filters()->attach($filter->id);
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