<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Storage;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $recordTitleAttribute = 'path';

    protected static ?string $title = 'فایل‌ها';

    public static function getModelLabel(): string
    {
        return __('business.files');
    }

    public static function getPluralModelLabel(): string
    {
        return __('business.files');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('path')
                    ->label(__('business.file'))
                    ->disk('s3')
                    ->directory('businesses/files')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'video/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(50 * 1024) // 50MB
                    ->required(),
                Select::make('type')
                    ->label(__('business.type'))
                    ->options([
                        'image' => __('business.image_type'),
                        'video' => __('business.video_type'),
                        'document' => __('business.document_type'),
                    ])
                    ->required(),
                Select::make('status')
                    ->label(__('business.status'))
                    ->options([
                        1 => __('business.active_status'),
                        0 => __('business.inactive_status'),
                    ])
                    ->default(1)
                    ->required(),
                TextInput::make('priority')
                    ->label(__('business.priority'))
                    ->numeric()
                    ->default(0)
                    ->helperText(__('business.lower_numbers_appear_first')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->recordTitleAttribute('path')
            ->columns([
                ImageColumn::make('path')
                    ->disk('s3')
                    ->circular()
                    ->size(40)
                    ->label(__('business.preview'))
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=File&color=7C3AED&background=EBF4FF'),
                TextColumn::make('type')
                    ->label(__('business.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'image' => 'success',
                        'video' => 'info',
                        'document' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label(__('business.status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == 1 ? __('business.active_status') : __('business.inactive_status'))
                    ->color(fn ($state): string => $state == 1 ? 'success' : 'danger'),
                TextColumn::make('file_size')
                    ->label(__('business.file_size'))
                    ->formatStateUsing(function ($record) {
                        if ($record->path) {
                            try {
                                // Try to get file size from S3 or local storage
                                $disk = Storage::disk('s3');
                                if ($disk->exists($record->path)) {
                                    $size = $disk->size($record->path);
                                    return number_format($size / 1024, 2) . ' KB';
                                }
                            } catch (\Exception $e) {
                                // Fallback to local storage
                                if (file_exists(storage_path('app/public/' . $record->path))) {
                                    $size = filesize(storage_path('app/public/' . $record->path));
                                    return number_format($size / 1024, 2) . ' KB';
                                }
                            }
                        }
                        return 'N/A';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priority')
                    ->label(__('business.priority'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : 'N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'image' => __('business.image_type'),
                        'video' => __('business.video_type'),
                        'document' => __('business.document_type'),
                    ]),
                SelectFilter::make('status')
                    ->options([
                        1 => __('business.active_status'),
                        0 => __('business.inactive_status'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('move_up')
                    ->label(__('business.move_up'))
                    ->icon('heroicon-o-arrow-up')
                    ->action(function ($record) {
                        $record->decrement('priority');
                    })
                    ->visible(fn ($record) => $record->priority > 0),
                Tables\Actions\Action::make('move_down')
                    ->label(__('business.move_down'))
                    ->icon('heroicon-o-arrow-down')
                    ->action(function ($record) {
                        $record->increment('priority');
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
