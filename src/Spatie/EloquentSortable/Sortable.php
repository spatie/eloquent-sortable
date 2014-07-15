<?php
namespace Spatie\EloquentSortable\Sortable;

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

        $highestOrderNumber = 0;

        if ($lastArticle = self::orderBy($this->sortable['order_column_name'], 'desc')->limit(1)->first([$this->sortable['order_column_name']])) {
            $highestOrderNumber = $lastArticle->order_column + 1;
        }

        return $highestOrderNumber;

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