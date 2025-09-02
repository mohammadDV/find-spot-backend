<?php

namespace App\Filament\Resources\FacilityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Domain\Business\Models\Business;

class BusinessesRelationManager extends RelationManager
{
    protected static string $relationship = 'businesses';

    protected static ?string $title = 'کسب و کارها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('business.title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('business.phone'))
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('business.email'))
                    ->email()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label(__('business.status'))
                    ->options([
                        'pending' => __('business.pending'),
                        'approved' => __('business.approved'),
                        'rejected' => __('business.rejected'),
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Toggle::make('active')
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
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('business.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('business.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('business.status'))
                    ->colors([
                        'warning' => Business::PENDING,
                        'success' => Business::APPROVED,
                        'danger' => Business::REJECT,
                    ]),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('business.active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('business.status'))
                    ->options([
                        Business::PENDING => __('business.pending'),
                        Business::APPROVED => __('business.approved'),
                        Business::REJECT => __('business.rejected'),
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('business.active'))
                    ->placeholder(__('business.all'))
                    ->trueLabel(__('business.active'))
                    ->falseLabel(__('business.inactive')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('business.attach_business'))
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        // Get the facility's categories
                        $facilityCategories = $this->ownerRecord->categories()->pluck('categories.id');

                        // If facility has no categories, return empty query
                        if ($facilityCategories->isEmpty()) {
                            return $query->whereRaw('1 = 0'); // Empty result
                        }

                        // Only show businesses that have at least one category in common with the facility
                        return $query->whereHas('categories', function (Builder $categoryQuery) use ($facilityCategories) {
                            $categoryQuery->whereIn('categories.id', $facilityCategories);
                        });
                    })
                    ->disabled(fn () => $this->ownerRecord->categories()->count() === 0)
                    ->tooltip(fn () => $this->ownerRecord->categories()->count() === 0
                        ? __('business.no_categories_assigned')
                        : null),
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
