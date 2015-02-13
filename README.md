Sortable behaviour for Eloquent models
=================

[![Build status](https://img.shields.io/travis/freekmurze/eloquent-sortable.svg)](https://travis-ci.org/freekmurze/eloquent-sortable)
[![Latest Version](https://img.shields.io/github/release/freekmurze/eloquent-sortable.svg?style=flat-square)](https://github.com/freekmurze/eloquent-sortable/releases)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/fb7765b9-7632-4897-8054-22d85b41ffda.svg)](https://insight.sensiolabs.com/projects/fb7765b9-7632-4897-8054-22d85b41ffda)
[![Quality Score](https://img.shields.io/scrutinizer/g/freekmurze/eloquent-sortable.svg?style=flat-square)](https://scrutinizer-ci.com/g/freekmurze/eloquent-sortable)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/eloquent-sortable.svg?style=flat-square)](https://packagist.org/packages/spatie/eloquent-sortable)

This package provides a trait that adds sortable behaviour to an Eloquent model.

The value of the ordercolumn of a new record of a model is determined by the maximum value of the ordercolumn of all records of that model + 1.

The package also provides a query scope to fetch all the records in the right order.

## Installation

This package can be installed through Composer.

```bash
composer require spatie/eloquent-sortable
```

You must add this service provider:
```php
// Laravel 4: app/config/app.php 
// Laravel 5: config/app.php

'providers' => [ 
	'...',
	'Spatie\EloquentSortable\SortableServiceProvider',
];
```

## Usage

To add sortable behaviour to your model you must:<br />
1. specify that the model will conform to ```Spatie\EloquentSortable\SortableInterface```<br />
2. use the trait ```Spatie\EloquentSortable\Sortable```<br />
3. specify which column will be used as the ordercolumn<br />

###example
```php
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableInterface;

class MyModel extends Eloquent implements SortableInterface
{

    use Sortable;

    public $sortable = [
        'order_column_name' => 'order_column',
    ];
    
    ...
}
```
If you don't set a value ```$sortable['order_column_name']``` the package will asume that your order column name will be 'order_column'; 


Assuming that the db-table for ```MyModel``` is empty:
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
You can set a new order for all the records using the ```setNewOrder```-method

```php
/**
 * the record for model id 3 will have record_column value 1
 * the record for model id 1 will have record_column value 2
 * the record for model id 2 will have record_column value 3
 */
MyModel::setNewOrder([3,1,2]);
```
