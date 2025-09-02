<?php

namespace App\Filament\Resources\FilterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;

class BusinessesRelationManager extends RelationManager
{
    protected static string $relationship = 'businesses';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'کسب‌وکارها';

    public static function getModelLabel(): string
    {
        return __('business.business');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.businesses');
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
                TextColumn::make('user.first_name')
                    ->label(__('business.owner'))
                    ->formatStateUsing(fn ($record) => $record->user ? $record->user->first_name . ' ' . $record->user->last_name : 'N/A')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('categories.title')
                    ->label(__('business.categories'))
                    ->formatStateUsing(fn ($record) => $record->categories->pluck('title')->join(', '))
                    ->limit(50)
                    ->toggleable(),
                BadgeColumn::make('status')
                    ->label(__('business.status'))
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => __('business.pending'),
                        'approved' => __('business.approved'),
                        'reject' => __('business.rejected'),
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'reject',
                    ]),
                BadgeColumn::make('active')
                    ->label(__('business.active'))
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
                SelectFilter::make('status')
                    ->label(__('business.status'))
                    ->options([
                        'pending' => __('business.pending'),
                        'approved' => __('business.approved'),
                        'reject' => __('business.rejected'),
                    ]),
                SelectFilter::make('active')
                    ->label(__('business.active'))
                    ->options([
                        1 => __('business.active'),
                        0 => __('business.inactive'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('business.attach_business'))
                    ->recordTitleAttribute('title')
                    ->form([
                        Select::make('recordId')
                            ->label(__('business.select_business'))
                            ->options(function () {
                                return \Domain\Business\Models\Business::whereNotNull('title')
                                    ->where('title', '!=', '')
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
