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
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class LikesRelationManager extends RelationManager
{
    protected static string $relationship = 'allLikes';

    protected static ?string $title = 'لایک ها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label(__('business.user'))
                    ->relationship('user', 'first_name', function ($query) {
                        return $query->whereNotNull('first_name')->where('first_name', '!=', '');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Toggle::make('is_like')
                    ->label(__('business.is_like'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.first_name')
            ->columns([
                TextColumn::make('user.first_name')
                    ->label(__('business.user'))
                    ->formatStateUsing(fn ($state, $record) => $record->user ? $record->user->first_name . ' ' . $record->user->last_name : 'N/A')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('is_like')
                    ->label(__('business.type'))
                    ->formatStateUsing(fn ($state) => $state ? __('business.like') : __('business.dislike'))
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_like')
                    ->label(__('business.filter_by_type'))
                    ->options([
                        true => __('business.like'),
                        false => __('business.dislike'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('business.add_like')),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label(__('business.delete')),
            ]);
    }
}
