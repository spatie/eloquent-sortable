<?php

namespace Spatie\EloquentSortable;

use Illuminate\Database\Query\Builder;

interface Sortable
{
    /**
     * Modify the order column value.
     */
    public function setHighestOrderNumber();

    /**
     * Let's be nice and provide an ordered scope.
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeOrdered($query);

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2,...
     *
     * @param array $ids
     *
     * @throws SortableException
     */
    public static function setNewOrder($ids);
}
