# Changelog

All notable changes to `eloquent-sortable` will be documented in this file

## 3.4.1

- fix deps

## 3.4.0

- add compatibility with Laravel 5.5

## 3.3.0 - 2017-04-16

- add `buildSortQuery()`

## 3.2.1 - 2017-01-23

- release without changes. Made to kickstart Packagist.

## 3.2.0 - 2017-01-23

- add compatibility with Laravel 5.4

## 3.1.0 - 2016-11-20

- added support for `SoftDeletes`

## 3.0.0 - 2016-10-22

- removed the need for a service provider
- some cleanup

## 2.3.0 - 2016-10-19

- added support for collections passed to `setNewOrder`

## 2.2.0 - 2016-10-19

- added `moveToStart`, `moveToEnd` and `swapOrder`

## 2.1.1 - 2016-03-21
- Fixed a bug in `moveOrderUp` (see #13)

## 2.1.0
- Added `moveOrderUp`- and `moveOrderDown`-methods

## 2.0.1
- Fixed typehinting on scope

## 2.0.0
- SortableInterface is now Sortable
- Sortable is now SortableTrait
- getHighestOrderNumber() now retrieves the highest existing order number (not a new one)
- setHighestOrderNumber() no longer requires a Sortable object parameter
- sort_when_creating option
- Added shouldSortWhenCreating function
- Added test coverage

## 1.1.2
- Removed typehinting on scope in interface.

## 1.1.1 (non-functional version!)
- Removed typehinting on scope

## 1.1.0
- Added an argument to `setNewOrder` to specify the starting order
- Adopted psr-2 and psr-4
