# Sortable behaviour for Eloquent models


[![Latest Version](https://img.shields.io/github/release/spatie/eloquent-sortable.svg?style=flat-square)](https://github.com/spatie/eloquent-sortable/releases)
[![Build Status](https://img.shields.io/travis/spatie/eloquent-sortable.svg?style=flat-square)](https://travis-ci.org/spatie/eloquent-sortable)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/eloquent-sortable.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/eloquent-sortable)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![StyleCI](https://styleci.io/repos/21866232/shield?branch=master)](https://styleci.io/repos/21866232)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/eloquent-sortable.svg?style=flat-square)](https://packagist.org/packages/spatie/eloquent-sortable)

This package provides a trait that adds sortable behaviour to an Eloquent model.

The value of the order column of a new record of a model is determined by the maximum value of the order column of all records of that model + 1.

The package also provides a query scope to fetch all the records in the right order.

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Installation

This package can be installed through Composer.

```
composer require spatie/eloquent-sortable
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

class MyModel extends Eloquent implements Sortable
{

    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];
    
    ...
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
 * the record for model id 3 will have record_column value 1
 * the record for model id 1 will have record_column value 2
 * the record for model id 2 will have record_column value 3
 */
MyModel::setNewOrder([3,1,2]);
```

Optionally you can pass the starting order number as the second argument.

```php
/**
 * the record for model id 3 will have record_column value 11
 * the record for model id 1 will have record_column value 12
 * the record for model id 2 will have record_column value 13
 */
MyModel::setNewOrder([3,1,2], 10);
```

To sort using a column other than the primary key, use the `setNewOrderByCustomColumn`-method.

```php
/**
 * the record for model uuid '7a051131-d387-4276-bfda-e7c376099715' will have record_column value 1
 * the record for model uuid '40324562-c7ca-4c69-8018-aff81bff8c95' will have record_column value 2
 * the record for model uuid '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1' will have record_column value 3
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
 * the record for model uuid '7a051131-d387-4276-bfda-e7c376099715' will have record_column value 10
 * the record for model uuid '40324562-c7ca-4c69-8018-aff81bff8c95' will have record_column value 11
 * the record for model uuid '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1' will have record_column value 12
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

You can swap the order of two models:

```php 
MyModel::swapOrder($myModel, $anotherModel);
```

### Grouping

If your model/table has a grouping field (usually a foreign key): `id, `**`user_id`**`, title, order_column`
and you'd like the above methods to take it into considerations, you can create a `buildSortQuery` method at your model:
```php
  public function buildSortQuery()
    {
        return static::query()->where('user_id', $this->user_id);
    }
```
This will restrict the calculations to fields value of the model instance.


## Tests

The package contains some integration/smoke tests, set up with Orchestra. The tests can be run via phpunit.

```bash
vendor/bin/phpunit
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://murze.be)
- [All Contributors](../../contributors)

## Alternatives
- [Listify](https://github.com/lookitsatravis/listify)
- [Rutorike-sortable](https://github.com/boxfrommars/rutorika-sortable)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

