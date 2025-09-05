<?php

namespace App\Filament\Resources\WeekendResource\Pages;

use App\Filament\Resources\WeekendResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeekend extends EditRecord
{
    protected static string $resource = WeekendResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
