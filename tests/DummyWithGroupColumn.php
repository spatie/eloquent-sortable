<?php

namespace Spatie\EloquentSortable\Test;

use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;

class DummyWithGroupColumn extends Model implements Sortable
{
    use SortableTrait;

    protected $table = 'dummies';
    protected $guarded = [];
    public $timestamps = false;
    public $sortable = [
        'group_column_name' => 'group_column',
        'sort_by_group' => true,
    ];
}
