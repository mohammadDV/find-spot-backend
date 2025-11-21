<?php

namespace App\Filament\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

/**
 * Base class for Filament ListRecords pages with automatic Redis caching.
 * 
 * Extend this class instead of ListRecords to enable automatic 1-minute caching
 * of all table queries through Redis.
 * 
 * Usage:
 * class ListUsers extends CachedListRecords
 * {
 *     protected static string $resource = UserResource::class;
 * }
 */
abstract class CachedListRecords extends ListRecords
{
    /**
     * Get the table query.
     * Override to apply caching if needed, but Filament handles pagination internally.
     * 
     * @return Builder
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery();
    }

    /**
     * Generate a cache key for the table query.
     * 
     * @param Builder $query
     * @param int $page
     * @param int $perPage
     * @return string
     */
    protected function generateCacheKey(Builder $query, int $page = 1, int $perPage = 25): string
    {
        $model = $query->getModel();
        $resource = static::$resource ?? get_class($this);
        
        $params = [
            'resource' => $resource,
            'model' => $model ? get_class($model) : 'unknown',
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'page' => $page,
            'perPage' => $perPage,
            'search' => request()->get('tableSearch'),
            'sortColumn' => request()->get('tableSortColumn'),
            'sortDirection' => request()->get('tableSortDirection'),
            'filters' => request()->get('tableFilters', []),
        ];
        
        return 'filament_table_cache:' . md5(json_encode($params));
    }
}

