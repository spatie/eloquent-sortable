<?php namespace Spatie\EloquentSortable\Sortable;

interface SortableInterface {
    public function validateModelProperties();
    public function setHighestOrderNumber($model);
    public function scopeOrdered($query);
} 