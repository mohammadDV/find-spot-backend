<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use Filament\Resources\Pages\CreateRecord;
use Domain\Business\Models\Business;
use Illuminate\Support\Facades\Log;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;

    private array $storedCategories = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure parent_category_id is removed before saving
        unset($data['parent_category_id']);

        // Store categories for later use and remove from data
        // Ensure categories is always an array
        $categories = $data['categories'] ?? [];
        $this->storedCategories = is_array($categories) ? $categories : [];
        unset($data['categories']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Handle categories relationship after business is created
        if (!empty($this->storedCategories)) {
            try {
                $this->record->categories()->attach($this->storedCategories);
            } catch (\Exception $e) {
                // Log error but don't fail the creation
                Log::error('Failed to attach categories to business: ' . $e->getMessage());
            }
        }
    }
}
