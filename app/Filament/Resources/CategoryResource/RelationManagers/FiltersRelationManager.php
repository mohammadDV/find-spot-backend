<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TernaryFilter;

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
                TextColumn::make('description')
                    ->label(__('business.description'))
                    ->limit(50)
                    ->toggleable(),
                BadgeColumn::make('status')
                    ->label(__('business.status'))
                    ->formatStateUsing(fn ($state) => $state ? __('business.active') : __('business.inactive'))
                    ->colors([
                        'success' => true,
                        'danger' => false,
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
                    ->label(__('business.attach_filter'))
                    ->recordTitleAttribute('title')
                    ->form([
                        \Filament\Forms\Components\Select::make('recordId')
                            ->label(__('business.select_filter'))
                            ->options(function () {
                                return \Domain\Business\Models\Filter::whereNotNull('title')
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
