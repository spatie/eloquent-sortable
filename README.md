# Sortable behaviour for Eloquent models


[![Latest Version](https://img.shields.io/github/release/spatie/eloquent-sortable.svg?style=flat-square)](https://github.com/spatie/eloquent-sortable/releases)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/fb7765b9-7632-4897-8054-22d85b41ffda.svg)](https://insight.sensiolabs.com/projects/fb7765b9-7632-4897-8054-22d85b41ffda)
[![Build Status](https://img.shields.io/travis/spatie/eloquent-sortable.svg?style=flat-square)](https://travis-ci.org/spatie/eloquent-sortable)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/eloquent-sortable.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/eloquent-sortable)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![StyleCI](https://styleci.io/repos/21866232/shield?branch=master)](https://styleci.io/repos/21866232)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/eloquent-sortable.svg?style=flat-square)](https://packagist.org/packages/spatie/eloquent-sortable)

This package provides a trait that adds sortable behaviour to an Eloquent model.

The value of the order column of a new record of a model is determined by the maximum value of the order column of all records of that model + 1.

The package also provides a query scope to fetch all the records in the right order.

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment you are required to send us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

The best postcards will get published on the open source page on our website.

## Installation

This package can be installed through Composer.

```
$ composer require spatie/eloquent-sortable
```

## Usage

To add sortable behaviour to your model you must:<br />
1. specify that the model will conform to ```Spatie\EloquentSortable\Sortable```<br />
2. use the trait ```Spatie\EloquentSortable\SortableTrait```<br />
3. specify which column will be used as the order column<br />

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

## Tests

The package contains some integration/smoke tests, set up with Orchestra. The tests can be run via phpunit.

```
$ vendor/bin/phpunit
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://murze.be)
- [All Contributors](../../contributors)

## Alternatives
- [Listify](https://github.com/lookitsatravis/listify)
- [Rutorike-sortable](https://github.com/boxfrommars/rutorika-sortable)

## About Spatie
Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

