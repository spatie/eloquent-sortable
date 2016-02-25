# Changelog

All Notable changes to `eloquent-sortable` will be documented in this file

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
