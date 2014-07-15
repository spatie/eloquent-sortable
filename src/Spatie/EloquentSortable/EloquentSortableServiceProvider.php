<?php namespace Spatie\EloquentSortable;

use Illuminate\Support\ServiceProvider;

class EloquentSortableServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;


	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->registerEvents();
	}


    /**
     *
     * Perform actions on Eloquent's Creating-event
     *
     */
    public function registerEvents() {

        $this->app['events']->listen('eloquent.creating*', function($model) {
            if ($model instanceof SortableInterface) {
                $model->validateModelProperties();
                $model->setHighestOrderNumber($model);
            }
        });

    }

}
