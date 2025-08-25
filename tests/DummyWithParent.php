<?php

namespace Spatie\EloquentSortable\Test;

use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class DummyWithParent extends DummyParentWithBuildSortQuery implements Sortable
{
    use SortableTrait;

    protected $table = 'dummies';
    protected $guarded = [];
    public $timestamps = false;
}
