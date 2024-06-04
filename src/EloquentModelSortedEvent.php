<?php

namespace Spatie\EloquentSortable;

use Illuminate\Database\Eloquent\Model;

class EloquentModelSortedEvent
{
    public string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function isFor(Model|string $model): bool
    {
        if (is_string($model)) {
            return $model === $this->model;
        }

        return get_class($model) === $this->model;
    }
}
