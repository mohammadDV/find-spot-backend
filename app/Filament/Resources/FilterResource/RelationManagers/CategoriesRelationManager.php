<?php

namespace App\Filament\Resources\FilterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\Select;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'دسته‌بندی‌ها';

    public static function getModelLabel(): string
    {
        return __('business.category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.categories');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
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
            ->filters([
                TernaryFilter::make('status')
                    ->label(__('business.status'))
                    ->placeholder(__('business.all'))
                    ->trueLabel(__('business.active'))
                    ->falseLabel(__('business.inactive')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('business.attach_category'))
                    ->recordTitleAttribute('title')
                    ->form([
                        Select::make('recordId')
                            ->label(__('business.select_category'))
                            ->options(function () {
                                return \Domain\Business\Models\Category::whereNotNull('title')
                                    ->where('title', '!=', '')
                                    ->where('status', 1)
                                    ->pluck('title', 'id');
                            })
                            ->searchable()
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label(__('business.detach')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label(__('business.detach_selected')),
                ]),
            ]);
    }
}
