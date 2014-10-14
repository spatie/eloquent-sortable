<?php
namespace Spatie\EloquentSortable;

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
        if (! isset($this->sortable['order_column_name']) OR $this->sortable['order_column_name'] == '')
        {
            return 'order_column';
        }
        else
        {
            return $this->sortable['order_column_name'];
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
         return ((int) self::max($this->sortable['order_column_name'])) + 1;
    }

    /**
     * Let's be nice and provide an ordered scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy($this->sortable['order_column_name']);
    }

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2, ...
     *
     * @param array $ids
     * @throws SortableException
     */
    public static function setNewOrder($ids)
    {
        if (! is_array($ids))
        {
            throw new SortableException('You must pass an array to setNewOrder');
        }

        $newOrder = 1;
        foreach($ids as $id)
        {
            $model = self::find($id);
            $model->order_column = $newOrder++;
            $model->save();
        }
    }
}