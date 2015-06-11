<?php
namespace Spatie\EloquentSortable;

use Illuminate\Database\Query\Builder;

trait Sortable
{
    /**
     * Modify the order column value
     *
     * @param $model
     */
    public function setHighestOrderNumber($model)
    {
        $orderColumnName = $this->determineOrderColumnName();
        $model->$orderColumnName = $this->getHighestOrderNumber();
    }

    /**
     * Determine the column name of the order column
     *
     * @return string
     */
    protected function determineOrderColumnName()
    {
        if (! isset($this->sortable['order_column_name']) || $this->sortable['order_column_name'] == '')
        {
            $orderColumnName =  'order_column';
        }
        else
        {
            $orderColumnName = $this->sortable['order_column_name'];
        }

        return $orderColumnName;
    }

    /**
     * Determine the order value for the new record
     *
     * @return int
     */
    public function getHighestOrderNumber()
    {
         return ((int) self::max($this->determineOrderColumnName())) + 1;
    }

    /**
     * Let's be nice and provide an ordered scope
     *
     * @param Builder $query
     * @return mixed
     */
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy($this->determineOrderColumnName());
    }

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2, ...
     * A starting order can be optionally supplied (defaults to 1).
     *
     * @param array $ids
     * @param integer $newOrder
     * @throws SortableException
     */
    public static function setNewOrder($ids, $newOrder = 1)
    {
        if (! is_array($ids))
        {
            throw new SortableException('You must pass an array to setNewOrder');
        }

        foreach($ids as $id)
        {
            $model = self::find($id);
            $orderColumnName = $model->determineOrderColumnName();
            $model->$orderColumnName = $newOrder++;
            $model->save();
        }
    }
}
