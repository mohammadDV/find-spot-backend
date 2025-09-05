<?php

namespace App\Filament\Resources\ReviewResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $title = 'فایل‌ها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('path')
                    ->label(__('business.file'))
                    ->disk('s3')
                    ->directory('reviews/files')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'video/*', 'application/pdf'])
                    ->maxSize(10 * 1024) // 10MB
                    ->required(),
                Select::make('type')
                    ->label(__('business.file_type'))
                    ->options([
                        'image' => __('business.image'),
                        'video' => __('business.video'),
                        'document' => __('business.document'),
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->columns([
                ViewColumn::make('preview')
                    ->label(__('business.preview'))
                    ->view('filament.components.file-preview')
                    ->viewData(fn ($record) => [
                        'path' => $record->path,
                        'type' => $record->type,
                    ]),
                TextColumn::make('path')
                    ->label(__('business.file_name'))
                    ->formatStateUsing(fn ($state) => basename($state))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('business.file_type'))
                    ->formatStateUsing(fn ($state) => match($state) {
                        'image' => __('business.image'),
                        'video' => __('business.video'),
                        'document' => __('business.document'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'image' => 'success',
                        'video' => 'warning',
                        'document' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label(__('business.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('business.add_file')),
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
}
