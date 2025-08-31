<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditBusiness extends EditRecord
{
    protected static string $resource = BusinessResource::class;

    private array $storedCategories = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

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

    protected function afterSave(): void
    {
        // Handle categories relationship after business is updated
        // Use sync to handle both attaching and detaching
        try {
            $this->record->categories()->sync($this->storedCategories);
        } catch (\Exception $e) {
            // Log error but don't fail the update
            Log::error('Failed to sync categories for business: ' . $e->getMessage());
        }
    }
}
