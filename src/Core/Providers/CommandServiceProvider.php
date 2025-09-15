<?php

namespace Core\Providers;

use Core\Console\Commands\AddPermissions;
use Core\Console\Commands\AddFiltersToCategory;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            AddPermissions::class,
            AddFiltersToCategory::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
