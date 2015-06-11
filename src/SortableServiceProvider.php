<?php namespace Spatie\EloquentSortable;

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
     * Register the service provider.
     */
    public function register()
    {
        $this->registerEvents();
    }

    /**
     * Perform actions on Eloquent's Creating-event.
     */
    public function registerEvents()
    {
        $this->app['events']->listen('eloquent.creating*', function ($model) {
            if ($model instanceof SortableInterface) {
                $model->setHighestOrderNumber($model);
            }
        });
    }
}
