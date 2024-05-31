<?php

namespace Spatie\EloquentSortable;

class EloquentModelSortedEvent
{
    public string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }
}
