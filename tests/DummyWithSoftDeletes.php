<?php

namespace Spatie\EloquentSortable\Test;

use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class DummyWithSoftDeletes extends Model implements Sortable
{
    use SoftDeletes, SortableTrait;

    protected $table = 'dummies';
    protected $guarded = [];
    public $timestamps = false;
}
