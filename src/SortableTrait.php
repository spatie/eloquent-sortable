<?php

namespace Spatie\EloquentSortable;

use ArrayAccess;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

trait SortableTrait
{
    public static function bootSortableTrait()
    {
        static::creating(function ($model) {
            if ($model instanceof Sortable && $model->shouldSortWhenCreating()) {
                $model->setHighestOrderNumber();
            }
        });
    }

    /**
     * Modify the order column value.
     */
    public function setHighestOrderNumber()
    {
        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    /**
     * Determine the Record with the highest order (located at the "End").
     *
     * @return int
     */
    public function getHighestOrderNumber(): int
    {
        if ($this->hasSortScope()) {
            return static::where($this->determineSortScope(), $this->{$this->determineSortScope()})
                ->max($this->determineOrderColumnName());
        }

        return (int) static::max($this->determineOrderColumnName());
    }

    /**
     * Let's be nice and provide an ordered scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOrdered(Builder $query, string $direction = 'asc')
    {
        return $query->orderBy($this->determineOrderColumnName(), $direction);
    }

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2, ...
     *
     * A starting order number can be optionally supplied (defaults to 1).
     *
     * @param array|\ArrayAccess $ids
     * @param int $startOrder
     */
    public static function setNewOrder($ids, int $startOrder = 1)
    {
        if (! is_array($ids) && ! $ids instanceof ArrayAccess) {
            throw new InvalidArgumentException('You must pass an array or ArrayAccess object to setNewOrder');
        }

        $model = new static;

        $orderColumnName = $model->determineOrderColumnName();
        $primaryKeyColumn = $model->getKeyName();

        foreach ($ids as $id) {
            static::withoutGlobalScope(SoftDeletingScope::class)
                ->where($primaryKeyColumn, $id)
                ->update([$orderColumnName => $startOrder++]);
        }
    }

    /*
     * Determine the column name of the order column.
     */
    protected function determineOrderColumnName(): string
    {
        if (
            isset($this->sortable['order_column_name']) &&
            ! empty($this->sortable['order_column_name'])
        ) {
            return $this->sortable['order_column_name'];
        }

        return 'order_column';
    }

    public function determineSortScope()
    {
        if (
            isset($this->sortable['sort_scope']) &&
            ! empty($this->sortable['sort_scope'])
        ) {
            return $this->sortable['sort_scope'];
        }

        return '';
    }

    public function hasSortScope()
    {
        return ! empty($this->determineSortScope());
    }

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool
    {
        return $this->sortable['sort_when_creating'] ?? true;
    }

    /**
     * Swaps the order of this model with the model 'below' this model.
     *
     * @return $this
     */
    public function moveOrderDown()
    {
        $orderColumnName = $this->determineOrderColumnName();

        $swapWithModel = static::limit(1)
            ->ordered()
            ->where($orderColumnName, '>', $this->$orderColumnName);

        if ($this->hasSortScope()) {
            $swapWithModel = $swapWithModel->where($this->determineSortScope(), $this->{$this->determineSortScope()});
        }

        $swapWithModel = $swapWithModel->first();

        if (! $swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    /**
     * Swaps the order of this model with the model 'above' this model.
     *
     * @return $this
     */
    public function moveOrderUp()
    {
        $orderColumnName = $this->determineOrderColumnName();

        $swapWithModel = static::limit(1)
            ->ordered('desc')
            ->where($orderColumnName, '<', $this->$orderColumnName);
        if ($this->hasSortScope()) {
            $swapWithModel = $swapWithModel->where($this->determineSortScope(), $this->{$this->determineSortScope()});
        }

        $swapWithModel = $swapWithModel->first();

        if (! $swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    /**
     * Swap the order of this model with the order of another model.
     *
     * @param \Spatie\EloquentSortable\Sortable $otherModel
     *
     * @return $this
     */
    public function swapOrderWithModel(Sortable $otherModel)
    {
        $orderColumnName = $this->determineOrderColumnName();

        $oldOrderOfOtherModel = $otherModel->$orderColumnName;

        $otherModel->$orderColumnName = $this->$orderColumnName;
        $otherModel->save();

        $this->$orderColumnName = $oldOrderOfOtherModel;
        $this->save();

        return $this;
    }

    /**
     * Swap the order of two models.
     *
     * @param \Spatie\EloquentSortable\Sortable $model
     * @param \Spatie\EloquentSortable\Sortable $otherModel
     */
    public static function swapOrder(Sortable $model, Sortable $otherModel)
    {
        $model->swapOrderWithModel($otherModel);
    }

    /**
     * Moves this model to the first position.
     *
     * @return $this
     */
    public function moveToStart()
    {
        $firstModel = static::limit(1)
            ->ordered()
            ->first();

        if ($firstModel->id === $this->id) {
            return $this;
        }

        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = $firstModel->$orderColumnName;
        $this->save();

        $fixOtherRecords = static::where($this->getKeyName(), '!=', $this->id);

        if ($this->hasSortScope()) {
            $fixOtherRecords = $fixOtherRecords->where($this->determineSortScope(), $this->{$this->determineSortScope()});
        }

        $fixOtherRecords->increment($orderColumnName);

        return $this;
    }

    /**
     * Moves this model to the last position.
     *
     * @return $this
     */
    public function moveToEnd()
    {
        $maxOrder = $this->getHighestOrderNumber();

        $orderColumnName = $this->determineOrderColumnName();

        if ($this->$orderColumnName === $maxOrder) {
            return $this;
        }

        $oldOrder = $this->$orderColumnName;

        $this->$orderColumnName = $maxOrder;
        $this->save();

        $fixOtherRecords = static::where($this->getKeyName(), '!=', $this->id)
            ->where($orderColumnName, '>', $oldOrder);

        if ($this->hasSortScope()) {
            $fixOtherRecords = $fixOtherRecords->where($this->determineSortScope(), $this->{$this->determineSortScope()});
        }

        $fixOtherRecords->decrement($orderColumnName);

        return $this;
    }
}
