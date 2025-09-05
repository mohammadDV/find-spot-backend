<?php

namespace App\Filament\Resources\ReviewResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $title = 'خدمات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_id')
                    ->label(__('business.service'))
                    ->relationship('services', 'title', function ($query) {
                        return $query->whereNotNull('title')->where('title', '!=', '');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('business.service'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.title')
                    ->label(__('business.category'))
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('business.status'))
                    ->formatStateUsing(fn ($state) => $state ? __('business.active') : __('business.inactive'))
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('business.filter_by_status'))
                    ->options([
                        1 => __('business.active'),
                        0 => __('business.inactive'),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label(__('business.detach_service')),
            ]);
    }
}
