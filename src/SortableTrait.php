<?php

namespace Spatie\EloquentSortable;

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Event;
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

    public function setHighestOrderNumber(): void
    {
        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    public function getHighestOrderNumber(): int
    {
        return (int)$this->buildSortQuery()->max($this->determineOrderColumnName());
    }

    public function getLowestOrderNumber(): int
    {
        return (int)$this->buildSortQuery()->min($this->determineOrderColumnName());
    }

    public function scopeOrdered(Builder $query, string $direction = 'asc')
    {
        return $query->orderBy($this->determineOrderColumnName(), $direction);
    }

    public static function setNewOrder(
        $ids,
        int $startOrder = 1,
        ?string $primaryKeyColumn = null,
        ?callable $modifyQuery = null
    ): void {
        if (! is_array($ids) && ! $ids instanceof ArrayAccess) {
            throw new InvalidArgumentException('You must pass an array or ArrayAccess object to setNewOrder');
        }

        $model = new static();

        $orderColumnName = $model->determineOrderColumnName();

        if (is_null($primaryKeyColumn)) {
            $primaryKeyColumn = $model->getQualifiedKeyName();
        }

        if (config('eloquent-sortable.ignore_timestamps', false)) {
            static::$ignoreTimestampsOn = array_values(array_merge(static::$ignoreTimestampsOn, [static::class]));
        }

        foreach ($ids as $id) {
            static::withoutGlobalScope(SoftDeletingScope::class)
                ->when(is_callable($modifyQuery), function ($query) use ($modifyQuery) {
                    return $modifyQuery($query);
                })
                ->where($primaryKeyColumn, $id)
                ->update([$orderColumnName => $startOrder++]);
        }

        Event::dispatch(new EloquentModelSortedEvent(static::class));

        if (config('eloquent-sortable.ignore_timestamps', false)) {
            static::$ignoreTimestampsOn = array_values(array_diff(static::$ignoreTimestampsOn, [static::class]));
        }
    }

    public static function setNewOrderByCustomColumn(string $primaryKeyColumn, $ids, int $startOrder = 1)
    {
        self::setNewOrder($ids, $startOrder, $primaryKeyColumn);
    }

    public function determineOrderColumnName(): string
    {
        return $this->sortable['order_column_name'] ?? config('eloquent-sortable.order_column_name', 'order_column');
    }

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool
    {
        return $this->sortable['sort_when_creating'] ?? config('eloquent-sortable.sort_when_creating', true);
    }

    public function moveAfter(Sortable $model): void
    {
        $orderColumnName = $this->determineOrderColumnName();

        $this->buildSortQuery()
            ->ordered()
            ->where($orderColumnName, '>', $model->$orderColumnName)
            ->increment($orderColumnName);

        $this->$orderColumnName = $model->$orderColumnName + 1;

        $this->saveQuietly();
    }

    public function moveBefore(Sortable $model): void
    {
        $orderColumnName = $this->determineOrderColumnName();

        $this->buildSortQuery()
            ->ordered()
            ->where($orderColumnName, '>=', $model->$orderColumnName)
            ->increment($orderColumnName);

        $this->$orderColumnName = $model->$orderColumnName;

        $this->saveQuietly();
    }

    public function moveOrderDown(): static
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

    public function moveOrderUp(): static
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

    public function swapOrderWithModel(Sortable $otherModel): static
    {
        $orderColumnName = $this->determineOrderColumnName();

        $oldOrderOfOtherModel = $otherModel->$orderColumnName;

        $otherModel->$orderColumnName = $this->$orderColumnName;
        $otherModel->save();

        $this->$orderColumnName = $oldOrderOfOtherModel;
        $this->save();

        return $this;
    }

    public static function swapOrder(Sortable $model, Sortable $otherModel): void
    {
        $model->swapOrderWithModel($otherModel);
    }

    public function moveToStart(): static
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

        $this->buildSortQuery()->where($this->getQualifiedKeyName(), '!=', $this->getKey())->increment(
            $orderColumnName
        );

        return $this;
    }

    public function moveToEnd(): static
    {
        $maxOrder = $this->getHighestOrderNumber();

        $orderColumnName = $this->determineOrderColumnName();

        if ($this->$orderColumnName === $maxOrder) {
            return $this;
        }

        $oldOrder = $this->$orderColumnName;

        $this->$orderColumnName = $maxOrder;
        $this->save();

        $this->buildSortQuery()->where($this->getQualifiedKeyName(), '!=', $this->getKey())
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

    public function buildSortQuery(): Builder
    {
        return static::query();
    }
}
