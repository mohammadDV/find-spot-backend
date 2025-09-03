<?php

namespace App\Filament\Resources\CityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Domain\Address\Models\Area;

class AreasRelationManager extends RelationManager
{
    protected static string $relationship = 'areas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('address.title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label(__('address.area_code'))
                    ->maxLength(10)
                    ->helperText(__('address.area_code_help')),
                Forms\Components\Textarea::make('description')
                    ->label(__('address.description'))
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label(__('address.active'))
                            ->default(true),
                        Forms\Components\TextInput::make('priority')
                            ->label(__('address.priority'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('address.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('address.area_code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\BadgeColumn::make('active')
                    ->label(__('address.status'))
                    ->formatStateUsing(fn ($state) => $state ? __('address.active') : __('address.inactive'))
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ]),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('address.priority'))
                    ->sortable()
                    ->badge()
                    ->color('warning'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('address.status'))
                    ->placeholder(__('address.all'))
                    ->trueLabel(__('address.active'))
                    ->falseLabel(__('address.inactive')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('address.create_area')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('address.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('address.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('address.delete_selected')),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }
}
