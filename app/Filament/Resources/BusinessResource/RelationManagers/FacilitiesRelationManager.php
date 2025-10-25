<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;

class FacilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'facilities';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'امکانات';

    public static function getModelLabel(): string
    {
        return __('business.facilities');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.facilities');
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
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->options(function () {
                        return \Domain\Business\Models\Category::whereNotNull('title')
                            ->where('title', '!=', '')
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $categoryId): Builder => $query->whereHas('categories', function (Builder $query) use ($categoryId) {
                                $query->where('categories.id', $categoryId);
                            })
                        );
                    })
                    ->label(__('business.category')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('business.attach_facility'))
                    ->form([
                        Select::make('facility_id')
                            ->label(__('business.select_facility'))
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
                                return \Domain\Business\Models\Facility::where('status', 1)
                                    ->whereHas('categories', function ($query) use ($businessCategoryIds) {
                                        $query->whereIn('categories.id', $businessCategoryIds);
                                    })
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder(__('business.select_facility_to_attach'))
                            ->helperText(__('business.only_facilities_for_business_categories')),
                    ])
                    ->action(function (array $data) {
                        // Get the parent record (business)
                        $business = $this->getOwnerRecord();

                        // Get the facility to attach
                        $facility = \Domain\Business\Models\Facility::find($data['facility_id']);

                        if ($facility && $business) {
                            // Attach the facility to the business
                            $business->facilities()->attach($facility->id);
                        }
                    }),
            ])
            // ->headerActions([
            //     Tables\Actions\AttachAction::make()
            //         ->recordTitleAttribute('title')
            //         ->label(__('business.attach_facility')),
            // ])
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
