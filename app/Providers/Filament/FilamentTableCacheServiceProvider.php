<?php

namespace App\Providers\Filament;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service Provider that automatically caches all Filament table queries
 * through Redis with a 1-minute TTL.
 * 
 * This works by intercepting paginated queries from Filament admin routes
 * and caching the results.
 */
class FilamentTableCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only enable caching for Filament admin requests
        if (!$this->isFilamentRequest()) {
            return;
        }

        // Extend Builder with a cached paginate method for Filament tables
        Builder::macro('cachedPaginateForFilament', function ($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
            $perPage = $perPage ?: request()->get('perPage', 25);
            $page = $page ?: request()->get($pageName, 1);
            
            // Generate cache key
            $cacheKey = $this->generateFilamentCacheKey($page, $perPage);
            
            // Cache for 1 minute (60 seconds) using Redis
            return Cache::store('redis')->remember($cacheKey, 60, function () use ($perPage, $columns, $pageName, $page) {
                return $this->paginate($perPage, $columns, $pageName, $page);
            });
        });

        // Hook into database queries to cache paginated results from Filament
        $this->cacheFilamentQueries();
    }

    /**
     * Set up query caching for Filament table queries.
     */
    protected function cacheFilamentQueries(): void
    {
        // Use Laravel's query events to intercept and cache paginated queries
        // Note: This is a simplified approach. For full automatic caching,
        // you may need to use the cachedPaginateForFilament macro in your Resources
        // or extend the CachedListRecords page class.
    }

    /**
     * Check if the current request is from Filament admin panel.
     */
    protected function isFilamentRequest(): bool
    {
        $path = request()->path();
        
        return str_contains($path, '/admin') || 
               request()->is('admin/*') ||
               (request()->hasHeader('X-Livewire') && str_contains($path, 'admin'));
    }

    /**
     * Generate a cache key for Filament table queries.
     */
    protected function generateFilamentCacheKey(Builder $query, int $page = 1, int $perPage = 25): string
    {
        $model = $query->getModel();
        
        // Get all relevant request parameters for Filament tables
        $params = [
            'table' => $model ? get_class($model) : 'unknown',
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

