<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use Domain\Event\Models\Event;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 14;

    public static function getNavigationLabel(): string
    {
        return __('site.events');
    }

    public static function getModelLabel(): string
    {
        return __('site.event');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.events');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('site.event_information'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('site.title'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('summary')
                            ->label(__('site.summary'))
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                        Textarea::make('information')
                            ->label(__('site.information'))
                            ->required()
                            ->rows(4)
                            ->maxLength(1000),
                        Textarea::make('description')
                            ->label(__('site.description'))
                            ->required()
                            ->rows(6)
                            ->maxLength(2000),
                    ]),

                Section::make(__('site.location'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('lat')
                                    ->label(__('site.latitude'))
                                    ->required()
                                    ->numeric()
                                    ->step(0.000001),
                                TextInput::make('long')
                                    ->label(__('site.longitude'))
                                    ->required()
                                    ->numeric()
                                    ->step(0.000001),
                            ]),
                        TextInput::make('address')
                            ->label(__('site.address'))
                            ->required()
                            ->maxLength(500),
                    ]),

                Section::make(__('site.event_details'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label(__('site.start_date'))
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('Y/m/d'),
                                DatePicker::make('end_date')
                                    ->label(__('site.end_date'))
                                    ->native(false)
                                    ->displayFormat('Y/m/d')
                                    ->after('start_date'),
                            ]),
                        TextInput::make('amount')
                            ->label(__('site.amount'))
                            ->maxLength(255)
                            ->helperText(__('site.event_amount_help')),
                    ]),

                Section::make(__('site.contact_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label(__('site.phone'))
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('email')
                                    ->label(__('site.email'))
                                    ->email()
                                    ->maxLength(255),
                            ]),
                        TextInput::make('website')
                            ->label(__('site.website'))
                            ->url()
                            ->maxLength(255),
                        TextInput::make('link')
                            ->label(__('site.event_link'))
                            ->url()
                            ->maxLength(255)
                            ->helperText(__('site.event_link_help')),
                    ]),

                Section::make(__('site.social_media'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('facebook')
                                    ->label(__('site.facebook'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('instagram')
                                    ->label(__('site.instagram'))
                                    ->url()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('youtube')
                                    ->label(__('site.youtube'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('whatsapp')
                                    ->label(__('site.whatsapp'))
                                    ->url()
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make(__('site.media'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('site.event_image'))
                            ->placeholder(__('site.upload_event_image'))
                            ->image()
                            ->imageEditor()
                            ->disk('s3')
                            ->directory('events/images')
                            ->visibility('public')
                            ->required()
                            ->columnSpan(1),
                        FileUpload::make('slider_image')
                            ->label(__('site.slider_image'))
                            ->placeholder(__('site.upload_slider_image'))
                            ->image()
                            ->imageEditor()
                            ->disk('s3')
                            ->directory('events/sliders')
                            ->visibility('public')
                            ->columnSpan(1),
                        FileUpload::make('video')
                            ->label(__('site.event_video'))
                            ->placeholder(__('site.upload_event_video'))
                            ->disk('s3')
                            ->directory('events/videos')
                            ->visibility('public')
                            ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/quicktime', 'video/wmv', 'video/flv', 'video/x-msvideo'])
                            ->maxSize(150 * 1024) // 150MB
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('site.settings'))
                    ->schema([
                        Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                0 => __('site.Inactive'),
                                1 => __('site.Active'),
                            ])
                            ->default(0)
                            ->required(),
                        Toggle::make('vip')
                            ->label(__('site.vip'))
                            ->default(false)
                            ->helperText(__('site.vip_event_help')),
                        TextInput::make('priority')
                            ->label(__('site.priority'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('site.priority_help')),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('site.table_id'))
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')
                    ->label(__('site.image'))
                    ->disk('s3')
                    ->visibility('public')
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->url(fn ($record) => $record->image ? $record->image : null)
                    ->circular()
                    ->size(40),
                TextColumn::make('title')
                    ->label(__('site.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('summary')
                    ->label(__('site.summary'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),
                TextColumn::make('start_date')
                    ->label(__('site.start_date'))
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('end_date')
                    ->label(__('site.end_date'))
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount')
                    ->label(__('site.amount'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(20),
                TextColumn::make('status')
                    ->label(__('site.status'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'danger',
                        1 => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => __('site.Inactive'),
                        1 => __('site.Active'),
                        default => $state,
                    }),
                IconColumn::make('vip')
                    ->label(__('site.vip'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('priority')
                    ->label(__('site.priority'))
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        0 => __('site.Inactive'),
                        1 => __('site.Active'),
                    ]),
                TernaryFilter::make('vip')
                    ->label(__('site.vip')),
                SelectFilter::make('priority')
                    ->label(__('site.priority'))
                    ->options([
                        0 => __('site.normal'),
                        1 => __('site.high'),
                        2 => __('site.very_high'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
