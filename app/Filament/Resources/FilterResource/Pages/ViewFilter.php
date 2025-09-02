<?php

namespace App\Filament\Resources\FilterResource\Pages;

use App\Filament\Resources\FilterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFilter extends ViewRecord
{
    protected static string $resource = FilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label(__('business.edit')),
            Actions\DeleteAction::make()
                ->label(__('business.delete')),
        ];
    }
}
