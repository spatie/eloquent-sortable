<?php

namespace Spatie\EloquentSortable\Test;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class DummyWithSortQuery extends Model implements Sortable
{
    use SortableTrait;

    protected $table = 'dummies';
    protected $guarded = [];
    public $timestamps = false;

    public function buildSortQuery()
    {
        return static::query()->where('fake_foreign_key_id', $this->fake_foreign_key_id);
    }
}
