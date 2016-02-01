<?php

namespace Spatie\EloquentSortable;

use Illuminate\Database\Eloquent\Model;
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
        $this->app['events']->listen('eloquent.creating*', function (Model $model) {
            if ($model instanceof Sortable
                and $model->shouldSortWhenCreating()
                and is_null($model->getAttribute($model->determineOrderColumnName()))
            ) {
                $model->setHighestOrderNumber();
            }
        });
    }
}
