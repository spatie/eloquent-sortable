<?php

namespace Spatie\EloquentSortable;

use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package;

class EloquentSortableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('eloquent-sortable')
            ->hasConfigFile();
    }
}
