<?php

namespace App\Filament\Resources\WeekendResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Grid;
use Domain\Business\Models\Business;

class BusinessesRelationManager extends RelationManager
{
    protected static string $relationship = 'businesses';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'کسب‌وکارها';

    public static function getModelLabel(): string
    {
        return __('site.business');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.businesses');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('business_id')
                            ->label(__('site.business'))
                            ->options(function () {
                                return Business::whereNotNull('title')
                                    ->where('title', '!=', '')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder(__('site.select_business'))
                            ->required()
                            ->helperText(__('site.select_business_to_attach')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('site.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('site.phone'))
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label(__('site.email'))
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                    ->searchable()
                    ->toggleable(),
                BadgeColumn::make('status')
                    ->label(__('site.status'))
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => __('site.pending'),
                        'approved' => __('site.approved'),
                        'reject' => __('site.rejected'),
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'reject',
                    ]),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('site.filter_by_status'))
                    ->options([
                        'pending' => __('site.pending'),
                        'approved' => __('site.approved'),
                        'reject' => __('site.rejected'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordTitleAttribute('title')
                    ->form([
                        Select::make('business_id')
                            ->label(__('site.business'))
                            ->options(function () {
                                return Business::whereNotNull('title')
                                    ->where('title', '!=', '')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder(__('site.select_business'))
                            ->required()
                            ->helperText(__('site.select_business_to_attach')),
                    ])
                    ->action(function (array $data) {
                        // Get the parent record (weekend)
                        $weekend = $this->getOwnerRecord();

                        // Get the business to attach
                        $business = Business::find($data['business_id']);

                        if ($business && $weekend) {
                            // Attach the business to the weekend
                            $weekend->businesses()->attach($business->id);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
