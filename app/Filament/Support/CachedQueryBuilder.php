<?php

namespace App\Filament\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Query Builder wrapper that automatically caches paginated results for Filament tables.
 */
class CachedQueryBuilder extends Builder
{
    protected $wrappedQuery;
    protected $resourceClass;

    public function __construct(Builder $query, string $resourceClass)
    {
        $this->wrappedQuery = $query;
        $this->resourceClass = $resourceClass;
        parent::__construct($query->getQuery());
        $this->setModel($query->getModel());
        $this->setEagerLoads($query->getEagerLoads());
    }

    /**
     * Paginate the query results with automatic caching.
     * 
     * @param int|null $perPage
     * @param array|string $columns
     * @param string $pageName
     * @param int|null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $perPage = $perPage ?: request()->get('perPage', 25);
        $page = $page ?: request()->get($pageName, 1);
        
        // Generate cache key
        $cacheKey = $this->generateCacheKey($page, $perPage);
        
        // Cache for 1 minute using Redis
        return Cache::store('redis')->remember($cacheKey, 60, function () use ($perPage, $columns, $pageName, $page) {
            return $this->wrappedQuery->paginate($perPage, $columns, $pageName, $page);
        });
    }

    /**
     * Generate a unique cache key for the query.
     */
    protected function generateCacheKey(int $page = 1, int $perPage = 25): string
    {
        $model = $this->getModel();
        
        $params = [
            'resource' => $this->resourceClass,
            'model' => $model ? get_class($model) : 'unknown',
            'sql' => $this->wrappedQuery->toSql(),
            'bindings' => $this->wrappedQuery->getBindings(),
            'page' => $page,
            'perPage' => $perPage,
            'search' => request()->get('tableSearch'),
            'sortColumn' => request()->get('tableSortColumn'),
            'sortDirection' => request()->get('tableSortDirection'),
            'filters' => request()->get('tableFilters', []),
        ];
        
        return 'filament_table_cache:' . md5(json_encode($params));
    }

    /**
     * Delegate all method calls to the wrapped query builder.
     */
    public function __call($method, $parameters)
    {
        $result = $this->wrappedQuery->$method(...$parameters);
        
        // Maintain method chaining - if the result is the wrapped builder, return $this
        if ($result === $this->wrappedQuery) {
            return $this;
        }
        
        return $result;
    }

    /**
     * Delegate property access to wrapped query.
     */
    public function __get($name)
    {
        return $this->wrappedQuery->$name;
    }

    /**
     * Delegate property setting to wrapped query.
     */
    public function __set($name, $value)
    {
        $this->wrappedQuery->$name = $value;
    }

    /**
     * Check if property exists on wrapped query.
     */
    public function __isset($name)
    {
        return isset($this->wrappedQuery->$name);
    }
}

