<?php

declare(strict_types=1);

namespace Spatie\EloquentSortable;

use ArrayAccess;
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
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder;

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2,...
     *
     * @param array|ArrayAccess $ids
     * @param int $startOrder
     */
    public static function setNewOrder(array|ArrayAccess $ids, int $startOrder = 1): void;

    /**
     * Modify the order column value for mass updates.
     *
     * @param array $ids
     * @param int $startOrder
     */
    public static function setMassNewOrder(array $ids, int $startOrder = 1): void;

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool;

    /**
     * Determine if the order column should be updated when updating a model instance.
     */
    public function shouldSortWhenUpdating(): bool;

    /**
     * Determine if the order column should be updated when deleting a model instance.
     */
    public function shouldSortWhenDeleting(): bool;
}
