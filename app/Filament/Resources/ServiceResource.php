<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use Domain\Business\Models\Service;
use Domain\Business\Models\Category;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('business.services');
    }

    public static function getModelLabel(): string
    {
        return __('business.service');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.services');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('business.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('business.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                Select::make('category_id')
                                    ->label(__('business.category'))
                                    ->options(function () {
                                        return Category::where('status', 1)
                                            ->whereNotNull('title')
                                            ->where('title', '!=', '')
                                            ->pluck('title', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('status')
                                    ->label(__('business.active'))
                                    ->default(true)
                                    ->helperText(__('business.status_help')),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('business.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('category.title')
                    ->label(__('business.category'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('service_votes_count')
                    ->label(__('business.votes'))
                    ->counts('serviceVotes')
                    ->sortable()
                    ->badge()
                    ->color('success'),
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
            ->searchable()
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('business.category'))
                    ->options(function () {
                        return Category::where('status', 1)
                            ->whereNotNull('title')
                            ->where('title', '!=', '')
                            ->pluck('title', 'id');
                    }),
                TernaryFilter::make('status')
                    ->label(__('business.status'))
                    ->placeholder(__('business.all'))
                    ->trueLabel(__('business.active'))
                    ->falseLabel(__('business.inactive')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('business.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('business.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('business.delete_selected')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default status
        if (!isset($data['status'])) {
            $data['status'] = 1;
        }

        return $data;
    }

    protected function mutateFormDataBeforeUpdate(array $data): array
    {
        return $data;
    }
}