<?php

namespace Spatie\EloquentSortable\Test;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class DummyCustom extends Model implements Sortable
{
    use SortableTrait;

    protected $table = 'dummy_customs';
    protected $guarded = [];
    public $timestamps = false;

    public $sortable = [
        'sort_when_creating' => true,
    ];
}
