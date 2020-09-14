<?php

namespace Spatie\EloquentSortable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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
