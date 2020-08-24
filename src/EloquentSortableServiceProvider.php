<?php

namespace Spatie\EloquentSortable;

use Illuminate\Support\ServiceProvider;

class EloquentSortableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/eloquent-sortable.php' => config_path('eloquent-sortable.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/eloquent-sortable.php', 'eloquent-sortable');

    }
}
