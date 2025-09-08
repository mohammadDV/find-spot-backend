<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

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
                    ->recordTitleAttribute('title')
                    ->label(__('business.attach_facility')),
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
