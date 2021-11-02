<?php

return [
    /*
     * Which column will be used as the order column.
     */
    'order_column_name' => 'order_column',

    /*
     * Define if the models should sort when creating.
     * When true, the package will automatically assign the highest order number to a new mode
     */
    'sort_when_creating' => true,

    /*
     * Define if the models should auto repair the order when a model is deleted.
     * When true, the package will automatically assign new orders to all models.
     *
     * For performance reasons, this is disabled by default. Only use it when you
     * want the order to never skip a number.
     */
    'repair_when_deleting' => false,
];
