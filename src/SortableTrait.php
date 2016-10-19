<?php

namespace Spatie\EloquentSortable;

trait SortableTrait
{
    /**
     * Modify the order column value.
     */
    public function setHighestOrderNumber()
    {
        $orderColumnName = $this->determineOrderColumnName();
        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    /**
     * Determine the order value for the new record.
     *
     * @return int
     */
    public function getHighestOrderNumber()
    {
        return (int) static::max($this->determineOrderColumnName());
    }

    /**
     * Let's be nice and provide an ordered scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $direction
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query, $direction = 'asc')
    {
        return $query->orderBy($this->determineOrderColumnName(), $direction);
    }

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2, ...
     *
     * A starting order number can be optionally supplied (defaults to 1).
     *
     * @param array $ids
     * @param int   $startOrder
     *
     * @throws SortableException
     */
    public static function setNewOrder($ids, $startOrder = 1)
    {
        if (! is_array($ids)) {
            throw new SortableException('You must pass an array to setNewOrder');
        }

        foreach ($ids as $id) {
            $model = static::find($id);
            $orderColumnName = $model->determineOrderColumnName();
            $model->$orderColumnName = $startOrder++;
            $model->save();
        }
    }

    /**
     * Determine the column name of the order column.
     *
     * @return string
     */
    protected function determineOrderColumnName()
    {
        if (
            isset($this->sortable['order_column_name']) &&
            ! empty($this->sortable['order_column_name'])
        ) {
            return $this->sortable['order_column_name'];
        }

        return 'order_column';
    }

    /**
     * Determine if the order column should be set when saving a new model instance.
     *
     * @return bool
     */
    public function shouldSortWhenCreating()
    {
        if (! isset($this->sortable)) {
            return true;
        }

        if (! isset($this->sortable['sort_when_creating'])) {
            return true;
        }

        return $this->sortable['sort_when_creating'];
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
            ->where($orderColumnName, '>', $this->$orderColumnName)
            ->first();

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
            ->where($orderColumnName, '<', $this->$orderColumnName)
            ->first();

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
    protected function swapOrderWithModel($otherModel)
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
     * @param \Spatie\EloquentSortable\Sortable $model
     */
    public static function swapOrder(self $model, self $model2)
    {
        $model->swapOrderWithModel($model2);
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

        static::where($this->getKeyName(), '!=', $this->id)
            ->increment($orderColumnName);

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

        static::where($this->getKeyName(), '!=', $this->id)
            ->where($orderColumnName, '>', $oldOrder)
            ->decrement($orderColumnName);

        return $this;
    }
}
