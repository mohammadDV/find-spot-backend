<?php

namespace App\Filament\Resources\FacilityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Domain\Business\Models\Category;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'دسته‌بندی‌ها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('business.title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('parent_id')
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
                    ->default(0),
                Forms\Components\Toggle::make('status')
                    ->label(__('business.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('business.title'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label(__('business.parent_category'))
                    ->formatStateUsing(fn ($state) => $state ?: __('business.root_category'))
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('status')
                    ->label(__('business.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('business.parent_category'))
                    ->options(function () {
                        return Category::where('parent_id', 0)
                            ->orWhereNull('parent_id')
                            ->whereNotNull('title')
                            ->where('title', '!=', '')
                            ->pluck('title', 'id')
                            ->prepend(__('business.root_category'), 0);
                    }),
                Tables\Filters\TernaryFilter::make('status')
                    ->label(__('business.status'))
                    ->placeholder(__('business.all'))
                    ->trueLabel(__('business.active'))
                    ->falseLabel(__('business.inactive')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('business.attach_category'))
                    ->preloadRecordSelect(),
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
