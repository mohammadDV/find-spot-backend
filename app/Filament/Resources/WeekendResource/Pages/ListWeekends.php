<?php

namespace App\Filament\Resources\WeekendResource\Pages;

use App\Filament\Resources\WeekendResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeekends extends ListRecords
{
    protected static string $resource = WeekendResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
