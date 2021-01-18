<?php

namespace Spatie\EloquentSortable;

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use InvalidArgumentException;

trait SortableTrait
{
    public static function bootSortableTrait()
    {
        static::creating(function ($model) {
            if ($model instanceof Sortable && $model->shouldSortWhenCreating()) {
                $model->setHighestOrderNumber();
            }
        });
    }

    public function setHighestOrderNumber()
    {
        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    public function getHighestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->max($this->determineOrderColumnName());
    }

    public function getLowestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->min($this->determineOrderColumnName());
    }

    public function scopeOrdered(Builder $query, string $direction = 'asc')
    {
        return $query->orderBy($this->determineOrderColumnName(), $direction);
    }

    public static function setNewOrder($ids, int $startOrder = 1, string $primaryKeyColumn = null)
    {
        if (! is_array($ids) && ! $ids instanceof ArrayAccess) {
            throw new InvalidArgumentException('You must pass an array or ArrayAccess object to setNewOrder');
        }

        $model = new static;

        $orderColumnName = $model->determineOrderColumnName();

        if (is_null($primaryKeyColumn)) {
            $primaryKeyColumn = $model->getKeyName();
        }

        foreach ($ids as $id) {
            static::withoutGlobalScope(SoftDeletingScope::class)
                ->where($primaryKeyColumn, $id)
                ->update([$orderColumnName => $startOrder++]);
        }
    }

    public static function setNewOrderByCustomColumn(string $primaryKeyColumn, $ids, int $startOrder = 1)
    {
        self::setNewOrder($ids, $startOrder, $primaryKeyColumn);
    }

    public function determineOrderColumnName(): string
    {
        return isset($this->sortable['order_column_name'])
            ? $this->sortable['order_column_name']
            : config('eloquent-sortable.order_column_name', 'order_column');
    }

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool
    {
        return isset($this->sortable['sort_when_creating'])
            ? $this->sortable['sort_when_creating']
            : config('eloquent-sortable.sort_when_creating', true);
    }

    public function moveOrderDown()
    {
        $orderColumnName = $this->determineOrderColumnName();

        $swapWithModel = $this->buildSortQuery()->limit(1)
            ->ordered()
            ->where($orderColumnName, '>', $this->$orderColumnName)
            ->first();

        if (! $swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    public function moveOrderUp()
    {
        $orderColumnName = $this->determineOrderColumnName();

        $swapWithModel = $this->buildSortQuery()->limit(1)
            ->ordered('desc')
            ->where($orderColumnName, '<', $this->$orderColumnName)
            ->first();

        if (! $swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    public function swapOrderWithModel(Sortable $otherModel)
    {
        $orderColumnName = $this->determineOrderColumnName();

        $oldOrderOfOtherModel = $otherModel->$orderColumnName;

        $otherModel->$orderColumnName = $this->$orderColumnName;
        $otherModel->save();

        $this->$orderColumnName = $oldOrderOfOtherModel;
        $this->save();

        return $this;
    }

    public static function swapOrder(Sortable $model, Sortable $otherModel)
    {
        $model->swapOrderWithModel($otherModel);
    }

    public function moveToStart()
    {
        $firstModel = $this->buildSortQuery()->limit(1)
            ->ordered()
            ->first();

        if ($firstModel->getKey() === $this->getKey()) {
            return $this;
        }

        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = $firstModel->$orderColumnName;
        $this->save();

        $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->getKey())->increment($orderColumnName);

        return $this;
    }

    public function moveToEnd()
    {
        $maxOrder = $this->getHighestOrderNumber();

        $orderColumnName = $this->determineOrderColumnName();

        if ($this->$orderColumnName === $maxOrder) {
            return $this;
        }

        $oldOrder = $this->$orderColumnName;

        $this->$orderColumnName = $maxOrder;
        $this->save();

        $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->getKey())
            ->where($orderColumnName, '>', $oldOrder)
            ->decrement($orderColumnName);

        return $this;
    }

    public function isLastInOrder(): bool
    {
        $orderColumnName = $this->determineOrderColumnName();

        return (int)$this->$orderColumnName === $this->getHighestOrderNumber();
    }

    public function isFirstInOrder(): bool
    {
        $orderColumnName = $this->determineOrderColumnName();

        return (int)$this->$orderColumnName === $this->getLowestOrderNumber();
    }

    public function buildSortQuery()
    {
        return static::query();
    }
}
