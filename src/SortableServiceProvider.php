<?php

namespace Spatie\EloquentSortable;

use Illuminate\Support\ServiceProvider;

class SortableServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The boot method.
     */
    public function boot()
    {
        $this->bootEvents();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
    }

    /**
     * Perform actions on Eloquent's creating-event.
     */
    public function bootEvents()
    {
        $this->app['events']->listen('eloquent.creating*', function ($model) {

            if ($model instanceof Sortable && $model->shouldSortWhenCreating()) {
                $model->setHighestOrderNumber();
            }
        });
    }
}
