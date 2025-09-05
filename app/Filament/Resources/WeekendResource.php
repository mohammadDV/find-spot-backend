<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeekendResource\Pages;
use App\Filament\Resources\WeekendResource\RelationManagers;
use Domain\Business\Models\Weekend;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class WeekendResource extends Resource
{
    protected static ?string $model = Weekend::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('site.weekends');
    }

    public static function getModelLabel(): string
    {
        return __('site.weekend');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.weekends');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('site.weekend_information'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('site.title'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Toggle::make('status')
                            ->label(__('site.status'))
                            ->default(true)
                            ->helperText(__('site.weekend_status_help')),
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
                    ->label(__('site.title'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn ($state) => $state ? __('site.active') : __('site.inactive'))
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),
                TextColumn::make('businesses_count')
                    ->label(__('site.businesses_count'))
                    ->counts('businesses')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable(),
            ])
            ->searchable()
            ->filters([
                SelectFilter::make('status')
                    ->label(__('site.filter_by_status'))
                    ->options([
                        1 => __('site.active'),
                        0 => __('site.inactive'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('site.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('site.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('site.delete_selected')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BusinessesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWeekends::route('/'),
            'create' => Pages\CreateWeekend::route('/create'),
            'edit' => Pages\EditWeekend::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
