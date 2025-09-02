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

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'کیفیت خدمت';

    public static function getModelLabel(): string
    {
        return __('business.service');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.services');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('id')
                    ->label(__('business.id'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('title')
                    ->label(__('business.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
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
            ]);
    }
}
