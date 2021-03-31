<?php

namespace Spatie\EloquentSortable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EloquentSortableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('eloquent-sortable')
            ->hasConfigFile();
    }
}
