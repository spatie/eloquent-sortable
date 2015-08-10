<?php

namespace Spatie\EloquentSortable;

interface Sortable
{
    /**
     * Modify the order column value.
     */
    public function setHighestOrderNumber();

    /**
     * Let's be nice and provide an ordered scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query);

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2,...
     *
     * @param array $ids
     *
     * @throws \Spatie\EloquentSortable\SortableException
     */
    public static function setNewOrder($ids);

    /**
     * Determine if the order column should be set when saving a new model instance.
     *
     * @return bool
     */
    public function shouldSortWhenCreating();
}
