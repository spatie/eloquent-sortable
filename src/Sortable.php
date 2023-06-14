<?php

namespace Spatie\EloquentSortable;

use Illuminate\Database\Eloquent\Builder;

interface Sortable
{
    /**
     * Modify the order column value.
     */
    public function setHighestOrderNumber(): void;

    /**
     * Let's be nice and provide an ordered scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOrdered(Builder $query);

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2,...
     *
     * @param array|\ArrayAccess $ids
     * @param int $startOrder
     */
    public static function setNewOrder($ids, int $startOrder = 1): void;

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool;

    /**
     * Determine the position for `sort_when_creating` is true
     */
    public function shouldMoveToStartWhenCreating(): bool;

    /**
     * Move the model to the start
     */
    public function moveToStart(): self;

    /**
     * Move the model to the end
     */
    public function moveToEnd(): self;
}
