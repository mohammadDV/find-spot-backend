<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'نظرات';

    public static function getModelLabel(): string
    {
        return __('business.reviews');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.reviews');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('comment')
                    ->label(__('business.content'))
                    ->required()
                    ->maxLength(65535),
                Select::make('rate')
                    ->label(__('business.rating'))
                    ->options([
                        1 => __('business.1_star'),
                        2 => __('business.2_stars'),
                        3 => __('business.3_stars'),
                        4 => __('business.4_stars'),
                        5 => __('business.5_stars'),
                    ])
                    ->required()
                    ->default(5),
                Select::make('active')
                    ->label(__('business.active'))
                    ->options([
                        1 => __('business.active'),
                        0 => __('business.inactive'),
                    ])
                    ->required()
                    ->default(1),
                Select::make('status')
                    ->label(__('business.status'))
                    ->options([
                        'pending' => __('business.pending'),
                        'approved' => __('business.approved'),
                        'cancelled' => __('business.cancelled'),
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('user.first_name')
                    ->label(__('business.user'))
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('comment   ')
                    ->label(__('business.comment'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->limit(200),
                TextColumn::make('rating')
                    ->label(__('business.rating'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1, 2 => 'danger',
                        3 => 'warning',
                        4, 5 => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => __('business.1_star'),
                        2 => __('business.2_stars'),
                        3 => __('business.3_stars'),
                        4 => __('business.4_stars'),
                        5 => __('business.5_stars'),
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => __('business.pending'),
                        'approved' => __('business.approved'),
                        'cancelled' => __('business.cancelled'),
                    ]),
                TernaryFilter::make('active')
                    ->label(__('business.active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
}
