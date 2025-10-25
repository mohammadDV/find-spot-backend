<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Filament\Resources\BusinessResource\RelationManagers;
use Domain\Business\Models\Business;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('site.businesses');
    }

    public static function getModelLabel(): string
    {
        return __('site.business');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.businesses');
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
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label(__('business.phone'))
                                    ->tel()
                                    ->maxLength(255),
                            ]),
                        Textarea::make('description')
                            ->label(__('business.description'))
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('email')
                                    ->label(__('business.email'))
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('website')
                                    ->label(__('business.website'))
                                    ->url()
                                    ->maxLength(255),
                            ]),
                        Textarea::make('address')
                            ->label(__('business.address'))
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('business.location_coordinates'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('country_id')
                                    ->label(__('business.country'))
                                    ->relationship('country', 'title', function ($query) {
                                        return $query->whereNotNull('title')->where('title', '!=', '');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('city_id')
                                    ->label(__('business.city'))
                                    ->relationship('city', 'title', function ($query) {
                                        return $query->whereNotNull('title')->where('title', '!=', '');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('area_id')
                                    ->label(__('business.area'))
                                    ->relationship('area', 'title', function ($query) {
                                        return $query->whereNotNull('title')->where('title', '!=', '');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('lat')
                                    ->label(__('business.lat'))
                                    ->required()
                                    ->helperText(__('business.enter_latitude'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // Update map when lat changes
                                    }),
                                TextInput::make('long')
                                    ->label(__('business.long'))
                                    ->required()
                                    ->helperText(__('business.enter_longitude'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // Update map when long changes
                                    }),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('business.social_media'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('facebook')
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('instagram')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('youtube')
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('tiktok')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                        TextInput::make('whatsapp')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->collapsible(),

                Section::make(__('business.business_details'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('start_amount')
                                    ->label(__('business.start_amount'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('$')
                                    ->helperText(__('business.enter_starting_price')),
                                Select::make('amount_type')
                                    ->label(__('business.amount_type'))
                                    ->options([
                                        1 => '$',
                                        2 => '$$',
                                        3 => '$$$',
                                        4 => '$$$$',
                                    ])
                                    ->default(0),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('business.working_hours'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_monday')
                                    ->label(__('business.monday') . ' ' . __('business.from'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                                TextInput::make('to_monday')
                                    ->label(__('business.monday') . ' ' . __('business.to'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_tuesday')
                                    ->label(__('business.tuesday') . ' ' . __('business.from'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                                TextInput::make('to_tuesday')
                                    ->label(__('business.tuesday') . ' ' . __('business.to'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_wednesday')
                                    ->label(__('business.wednesday') . ' ' . __('business.from'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                                TextInput::make('to_wednesday')
                                    ->label(__('business.wednesday') . ' ' . __('business.to'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_thursday')
                                    ->label(__('business.thursday') . ' ' . __('business.from'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                                TextInput::make('to_thursday')
                                    ->label(__('business.thursday') . ' ' . __('business.to'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_friday')
                                    ->label(__('business.friday') . ' ' . __('business.from'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                                TextInput::make('to_friday')
                                    ->label(__('business.friday') . ' ' . __('business.to'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_saturday')
                                    ->label(__('business.saturday') . ' ' . __('business.from'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                                TextInput::make('to_saturday')
                                    ->label(__('business.saturday') . ' ' . __('business.to'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_sunday')
                                    ->label(__('business.sunday') . ' ' . __('business.from'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                                TextInput::make('to_sunday')
                                    ->label(__('business.sunday') . ' ' . __('business.to'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->helperText(__('business.enter_hour_0_23')),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('business.media_files'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('business.image'))
                            ->image()
                            ->disk('s3')
                            ->directory('businesses/images')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        FileUpload::make('menu_image')
                            ->label(__('business.menu_image'))
                            ->image()
                            ->disk('s3')
                            ->directory('businesses/menus')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        FileUpload::make('slider_image')
                            ->label(__('business.slider_image'))
                            ->image()
                            ->disk('s3')
                            ->directory('businesses/sliders')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        FileUpload::make('video')
                            ->label(__('business.video'))
                            ->disk('s3')
                            ->directory('businesses/videos')
                            ->visibility('public')
                            ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/quicktime', 'video/wmv', 'video/webm', 'video/3gpp', 'video/x-msvideo'])
                            ->maxSize(100 * 1024) // 100MB
                            ->helperText(__('Maximum file size: 100MB. Supported formats: MP4, AVI, MOV, WMV, WEBM'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('business.settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('business.user'))
                                    ->relationship('user', 'first_name', function ($query) {
                                        return $query->whereNotNull('first_name')->where('first_name', '!=', '');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('status')
                                    ->label(__('business.status'))
                                    ->options([
                                        'pending' => __('business.pending'),
                                        'approved' => __('business.approved'),
                                        'rejected' => __('business.rejected'),
                                    ])
                                    ->default(__('business.pending'))
                                    ->required(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Toggle::make('active')
                                    ->label(__('business.active'))
                                    ->default(true),
                                Toggle::make('vip')
                                    ->label(__('business.vip'))
                                    ->default(false),
                                TextInput::make('priority')
                                    ->label(__('business.priority'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('point')
                                    ->label(__('business.point'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                TextInput::make('rate')
                                    ->label(__('business.rate'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(5),
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
                // Simplified table for testing
                TextColumn::make('title')
                    ->label(__('business.title'))
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('business.phone'))
                    ->formatStateUsing(fn ($state) => $state ?: 'N/A')
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label(__('business.status'))
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => __('business.pending'),
                        'approved' => __('business.approved'),
                        'reject' => __('business.rejected'),
                        default => $state,
                    })
                    ->colors([
                        'warning' => Business::PENDING,
                        'success' => Business::APPROVED,
                        'danger' => Business::REJECT,
                    ]),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable(),
            ])
            ->searchable()
            ->filters([
                SelectFilter::make('status')
                    ->label(__('business.filter_by_status'))
                    ->options([
                        Business::PENDING => __('business.pending'),
                        Business::APPROVED => __('business.approved'),
                        Business::REJECT => __('business.rejected'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('business.edit')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('business.delete_selected')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FilesRelationManager::class,
            RelationManagers\CategoriesRelationManager::class,
            RelationManagers\FacilitiesRelationManager::class,
            RelationManagers\FiltersRelationManager::class,
            RelationManagers\TagsRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'view' => Pages\ViewBusiness::route('/{record}'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure all required fields have values
        $data['user_id'] = $data['user_id'] ?? null;
        $data['country_id'] = $data['country_id'] ?? null;
        $data['city_id'] = $data['city_id'] ?? null;
        $data['area_id'] = $data['area_id'] ?? null;

        // Ensure filters is an array
        if (!isset($data['filters'])) {
            $data['filters'] = [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure all required fields have values before creation
        $data['user_id'] = $data['user_id'] ?? null;
        $data['country_id'] = $data['country_id'] ?? null;
        $data['city_id'] = $data['city_id'] ?? null;
        $data['area_id'] = $data['area_id'] ?? null;

        return $data;
    }

    protected function mutateFormDataBeforeUpdate(array $data): array
    {
        // Ensure all required fields have values before update
        $data['user_id'] = $data['user_id'] ?? null;
        $data['country_id'] = $data['country_id'] ?? null;
        $data['city_id'] = $data['city_id'] ?? null;
        $data['area_id'] = $data['area_id'] ?? null;

        return $data;
    }
}
