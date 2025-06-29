<?php

namespace Spatie\EloquentSortable\Test;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DummyParentWithBuildSortQuery extends Model
{
    public function buildSortQuery(): Builder
    {
        return static::query()->where('id', '>=', 5);
    }
}
