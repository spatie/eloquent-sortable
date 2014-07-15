<?php
namespace Spatie\EloquentSortable;

trait Sortable
{

    /**
     *
     * Validate if the right properties are set on the model
     *
     * @throws SortableException
     */
    public function validateModelProperties()
    {
        if (! isset($this->sortable['order_column_name']) OR $this->sortable['order_column_name'] == '')
        {
            throw new SortableException('You must specifiy the name of the ordercolumn');
        }
    }


    /**
     *
     * Modify the order column value
     *
     * @param $model
     */
    public function setHighestOrderNumber($model)
    {
        $orderColumnName = $this->sortable['order_column_name'];
        $model->$orderColumnName = $this->getHighestOrderNumber();

    }

    /**
     *
     * Determine the order value for the new record
     *
     * @return int
     */
    public function getHighestOrderNumber()
    {

         return ((int) self::max($this->sortable['order_column_name'])) + 1;

    }

    /**
     *
     * Let's be nice and provide an ordered scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeOrdered($query)
    {

        return $query->orderBy($this->sortable['order_column_name']);

    }
}