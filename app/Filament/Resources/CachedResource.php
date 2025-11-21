<?php

namespace App\Filament\Resources;

use App\Filament\Support\CachedQueryBuilder;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

/**
 * Base Resource class with automatic Redis caching for table queries.
 * 
 * Extend this class instead of Resource to enable automatic 1-minute caching
 * of all table queries through Redis.
 * 
 * Usage:
 * class UserResource extends CachedResource
 * {
 *     protected static ?string $model = User::class;
 *     // ... rest of your resource code
 * }
 */
abstract class CachedResource extends Resource
{
    /**
     * Get the Eloquent query for listing/filtering records.
     * This automatically caches paginated results with a 1-minute TTL.
     * 
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Wrap the query to cache paginated results
        return new CachedQueryBuilder($query, static::class);
    }
}

