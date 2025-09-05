<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use Domain\Review\Models\Review;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Domain\Notification\Services\NotificationService;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('site.reviews');
    }

    public static function getModelLabel(): string
    {
        return __('site.review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.reviews');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('business.review_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('business.reviewer'))
                                    ->relationship('user', 'first_name', function ($query) {
                                        return $query->whereNotNull('first_name')->where('first_name', '!=', '');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('business_id')
                                    ->label(__('business.business'))
                                    ->relationship('business', 'title', function ($query) {
                                        return $query->whereNotNull('title')->where('title', '!=', '');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                        TextInput::make('rate')
                            ->label(__('business.rate'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1)
                            ->helperText(__('business.rate_1_to_5')),
                        Textarea::make('comment')
                            ->label(__('business.comment'))
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(4),
                    ])
                    ->collapsible(),

                Section::make(__('business.review_settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label(__('business.review_status'))
                                    ->options([
                                        Review::PENDING => __('business.pending'),
                                        Review::APPROVED => __('business.approved'),
                                        Review::CANCELLED => __('business.cancelled'),
                                    ])
                                    ->default(Review::PENDING)
                                    ->required(),
                                Toggle::make('active')
                                    ->label(__('business.active'))
                                    ->default(true),
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
                TextColumn::make('id')
                    ->label(__('#'))
                    ->sortable(),
                TextColumn::make('user.first_name')
                    ->label(__('business.reviewer'))
                    ->formatStateUsing(fn ($state, $record) => $record->user ? $record->user->first_name . ' ' . $record->user->last_name : 'N/A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('business.title')
                    ->label(__('business.business'))
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rate')
                    ->label(__('business.rate'))
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('★', $state) . ' (' . $state . '/5)' : 'N/A')
                    ->sortable(),
                TextColumn::make('comment')
                    ->label(__('business.comment'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state ? (strlen($state) > 50 ? substr($state, 0, 50) . '...' : $state) : 'N/A')
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label(__('business.review_status'))
                    ->formatStateUsing(fn ($state) => match($state) {
                        Review::PENDING => __('business.pending'),
                        Review::APPROVED => __('business.approved'),
                        Review::CANCELLED => __('business.cancelled'),
                        default => $state,
                    })
                    ->colors([
                        'warning' => Review::PENDING,
                        'success' => Review::APPROVED,
                        'danger' => Review::CANCELLED,
                    ]),
                TextColumn::make('active')
                    ->label(__('business.active'))
                    ->formatStateUsing(fn ($state) => $state ? __('business.yes') : __('business.no'))
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                TextColumn::make('likes_count')
                    ->label(__('business.likes_count'))
                    ->counts('likes')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable(),
            ])
            ->searchable()
            ->filters([
                SelectFilter::make('status')
                    ->label(__('business.filter_by_status'))
                    ->options([
                        Review::PENDING => __('business.pending'),
                        Review::APPROVED => __('business.approved'),
                        Review::CANCELLED => __('business.cancelled'),
                    ]),
                SelectFilter::make('active')
                    ->label(__('business.filter_by_active'))
                    ->options([
                        1 => __('business.yes'),
                        0 => __('business.no'),
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label(__('business.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === Review::PENDING)
                    ->requiresConfirmation()
                    ->modalHeading(__('business.approve_review'))
                    ->modalDescription(__('business.approve_review_confirmation'))
                    ->action(function ($record) {
                        $record->update(['status' => Review::APPROVED]);

                        // Send notification to the user
                        NotificationService::create([
                            'title' => __('site.review_approved_title'),
                            'content' => __('site.review_approved_content'),
                            'id' => $record->id,
                            'type' => NotificationService::REVIEW,
                        ], $record->user);

                        \Filament\Notifications\Notification::make()
                            ->title(__('business.review_approved_successfully'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FilesRelationManager::class,
            RelationManagers\LikesRelationManager::class,
            RelationManagers\ServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('likes');
    }
}
