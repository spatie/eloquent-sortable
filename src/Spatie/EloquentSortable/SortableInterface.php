<?php namespace Spatie\EloquentSortable;

interface SortableInterface {
    public function validateModelProperties();
    public function setHighestOrderNumber($model);
    public function scopeOrdered($query);
} 