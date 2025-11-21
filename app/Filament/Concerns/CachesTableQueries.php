<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

trait CachesTableQueries
{
    /**
     * Get the Eloquent query for this resource.
     * This method is called by Filament to get the base query for tables.
     * 
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // The actual caching happens at the ListRecords page level
        // This trait just marks the resource as cacheable
        return $query;
    }

    /**
     * Generate a cache key for the current table query.
     * 
     * @param Builder $query
     * @param int $page
     * @param int $perPage
     * @return string
     */
    public static function generateTableCacheKey(Builder $query, int $page = 1, int $perPage = 25): string
    {
        $model = static::getModel();
        $resource = static::class;
        
        $params = [
            'resource' => $resource,
            'model' => get_class($model),
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

