<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=eloquent-sortable">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/eloquent-sortable/html/dark.webp">
        <img alt="Logo for eloquent-sortable" src="https://spatie.be/packages/header/eloquent-sortable/html/light.webp">
      </picture>
    </a>

<h1>Sortable behaviour for Eloquent models</h1>

[![Latest Version](https://img.shields.io/github/release/spatie/eloquent-sortable.svg?style=flat-square)](https://github.com/spatie/eloquent-sortable/releases)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/spatie/eloquent-sortable/run-tests.yml?branch=main&label=tests)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/eloquent-sortable.svg?style=flat-square)](https://packagist.org/packages/spatie/eloquent-sortable)
    
</div>

This package provides a trait that adds sortable behaviour to an Eloquent model.

The value of the order column of a new record of a model is determined by the maximum value of the order column of all records of that model + 1.

The package also provides a query scope to fetch all the records in the right order.

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Support us

Learn how to create a package like this one, by watching our premium video course:

[![Laravel Package training](https://spatie.be/github/package-training.jpg)](https://laravelpackage.training)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

> For Laravel 6.x or PHP 7.x, use version 3.x of this package.

This package can be installed through Composer.

```
composer require spatie/eloquent-sortable
```

In Laravel 5.5 and above the service provider will automatically get registered. In older versions of the framework just add the service provider in `config/app.php` file:

```php
'providers' => [
    ...
    Spatie\EloquentSortable\EloquentSortableServiceProvider::class,
];
```

Optionally you can publish the config file with:

```bash
php artisan vendor:publish --tag=eloquent-sortable-config
```

This is the content of the file that will be published in `config/eloquent-sortable.php`

```php
return [
  /*
   * The name of the column that will be used to sort models.
   */
  'order_column_name' => 'order_column',

  /*
   * Define if the models should sort when creating. When true, the package
   * will automatically assign the highest order number to a new model
   */
  'sort_when_creating' => true,

  /*
   * Define if the timestamps should be ignored when sorting.
   * When true, updated_at will not be updated when using setNewOrder
   */
  'ignore_timestamps' => false,
];
```

## Usage

To add sortable behaviour to your model you must:
1. Implement the `Spatie\EloquentSortable\Sortable` interface.
2. Use the trait `Spatie\EloquentSortable\SortableTrait`.
3. Optionally specify which column will be used as the order column. The default is `order_column`.

### Example

```php
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MyModel extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    // ...
}
```

If you don't set a value `$sortable['order_column_name']` the package will assume that your order column name will be named `order_column`.

If you don't set a value `$sortable['sort_when_creating']` the package will automatically assign the highest order number to a new model;

Assuming that the db-table for `MyModel` is empty:

```php
$myModel = new MyModel();
$myModel->save(); // order_column for this record will be set to 1

$myModel = new MyModel();
$myModel->save(); // order_column for this record will be set to 2

$myModel = new MyModel();
$myModel->save(); // order_column for this record will be set to 3


//the trait also provides the ordered query scope
$orderedRecords = MyModel::ordered()->get();
```

You can set a new order for all the records using the `setNewOrder`-method

```php
/**
 * the record for model id 3 will have order_column value 1
 * the record for model id 1 will have order_column value 2
 * the record for model id 2 will have order_column value 3
 */
MyModel::setNewOrder([3,1,2]);
```

Optionally you can pass the starting order number as the second argument.

```php
/**
 * the record for model id 3 will have order_column value 11
 * the record for model id 1 will have order_column value 12
 * the record for model id 2 will have order_column value 13
 */
MyModel::setNewOrder([3,1,2], 10);
```

You can modify the query that will be executed by passing a closure as the fourth argument.

```php
/**
 * the record for model id 3 will have order_column value 11
 * the record for model id 1 will have order_column value 12
 * the record for model id 2 will have order_column value 13
 */
MyModel::setNewOrder([3,1,2], 10, null, function($query) {
    $query->withoutGlobalScope(new ActiveScope);
});
```


To sort using a column other than the primary key, use the `setNewOrderByCustomColumn`-method.

```php
/**
 * the record for model uuid '7a051131-d387-4276-bfda-e7c376099715' will have order_column value 1
 * the record for model uuid '40324562-c7ca-4c69-8018-aff81bff8c95' will have order_column value 2
 * the record for model uuid '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1' will have order_column value 3
 */
MyModel::setNewOrderByCustomColumn('uuid', [
   '7a051131-d387-4276-bfda-e7c376099715',
   '40324562-c7ca-4c69-8018-aff81bff8c95',
   '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1'
]);
```

As with `setNewOrder`, `setNewOrderByCustomColumn` will also accept an optional starting order argument.

```php
/**
 * the record for model uuid '7a051131-d387-4276-bfda-e7c376099715' will have order_column value 10
 * the record for model uuid '40324562-c7ca-4c69-8018-aff81bff8c95' will have order_column value 11
 * the record for model uuid '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1' will have order_column value 12
 */
MyModel::setNewOrderByCustomColumn('uuid', [
   '7a051131-d387-4276-bfda-e7c376099715',
   '40324562-c7ca-4c69-8018-aff81bff8c95',
   '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1'
], 10);
```

You can also move a model up or down with these methods:

```php
$myModel->moveOrderDown();
$myModel->moveOrderUp();
```

You can also move a model to the first or last position:

```php
$myModel->moveToStart();
$myModel->moveToEnd();
```

You can determine whether an element is first or last in order:

```php
$myModel->isFirstInOrder();
$myModel->isLastInOrder();
```

You can swap the order of two models:

```php
MyModel::swapOrder($myModel, $anotherModel);
```

### Grouping

If your model/table has a grouping field (usually a foreign key): `id, `**`user_id`**`, title, order_column`
and you'd like the above methods to take it into considerations, you can create a `buildSortQuery` method at your model:
```php
// MyModel.php

public function buildSortQuery()
{
    return static::query()->where('user_id', $this->user_id);
}
```
This will restrict the calculations to fields value of the model instance.

### Dispatched events

Once a sort has been completed, an event (`Spatie\EloquentSortable\EloquentModelSortedEvent`) is dispatched that you
can listen for. This can be useful for running post-sorting logic such as clearing caches or other actions that
need to be taken after a sort.

The event has an `isFor` helper which allows you to conveniently check the Eloquent class that has been sorted.

Below is an example of how you can listen for this event:

```php
use Spatie\EloquentSortable\EloquentModelSortedEvent as SortEvent;

class SortingListener
{
    public function handle(SortEvent $event): void {
        if ($event->isFor(MyClass::class)) {
            // ToDo: flush our cache
        }
    }
}
```

## Tests

The package contains some integration/smoke tests, set up with Orchestra. The tests can be run via phpunit.

```bash
vendor/bin/phpunit
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## Alternatives
- [Listify](https://github.com/lookitsatravis/listify)
- [Rutorike-sortable](https://github.com/boxfrommars/rutorika-sortable)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
