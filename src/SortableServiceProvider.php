<?php

namespace Spatie\EloquentSortable;

use Illuminate\Support\ServiceProvider;

/**
 * @deprecated
 *
 * Previous version of the package need this service provided to hook into the
 * creating event of an eloquent model. This functionality has been moved
 * to the `bootSortableTrait` method of `SortableTrait`. This service
 * provider will be removed in the next major version.
 *
 * Class SortableServiceProvider
 */
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
     * @deprecated
     */
    public function bootEvents()
    {
    }
}
