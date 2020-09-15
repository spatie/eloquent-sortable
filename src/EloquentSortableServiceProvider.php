<?php

namespace Spatie\EloquentSortable;

use Illuminate\Support\ServiceProvider;

class EloquentSortableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/eloquent-sortable.php' => config_path('eloquent-sortable.php'),
            ], 'config');
        }
    }
}
