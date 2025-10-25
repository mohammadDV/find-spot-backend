<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use Filament\Resources\Pages\CreateRecord;
use Domain\Business\Models\Business;
use Illuminate\Support\Facades\Log;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;

    // Categories are handled through the RelationManager, not the main form
}
