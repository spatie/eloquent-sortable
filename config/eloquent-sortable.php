<?php

declare(strict_types=1);

return [
    /*
     * Which column will be used as the order column.
     */
    'order_column_name' => 'order_column',

    /*
     * Define if the models should sort when creating.
     * When true, the package will automatically assign the highest order number to a new model.
     */
    'sort_when_creating' => true,

    /*
     * Define if the models should sort when updating.
     * When true, the package will automatically update the order of models when one is updated.
     */
    'sort_when_updating' => true,

    /*
     * Define if the models should sort when deleting.
     * When true, the package will automatically update the order of models when one is deleted.
     */
    'sort_when_deleting' => true,

    /*
     * Define if the timestamps should be ignored when sorting.
     * When true, `updated_at` will not be updated when using `setNewOrder`, `setMassNewOrder`,
     * or when models are reordered automatically during creation, updating, or deleting.
     */
    'ignore_timestamps' => false,
];
