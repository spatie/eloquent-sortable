<?php

declare(strict_types=1);

namespace Spatie\EloquentSortable;

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

trait SortableTrait
{
    public array $sortables = [];

    public static function bootSortableTrait(): void
    {
        static::creating(function (Model $model) {
            if (($model instanceof Sortable || $model instanceof Model) && $model->shouldSortWhenCreating()) {
                $model->setHighestOrderNumber();
            }
        });

        static::updating(function (Model $model): void {
            if (($model instanceof Sortable || $model instanceof Model) && $model->shouldSortWhenUpdating()) {
                self::setMassNewOrder($model->sortables);
            }
        });

        static::deleting(function (Model $model): void {
            if (($model instanceof Sortable || $model instanceof Model) && $model->shouldSortWhenDeleting()) {
                self::setMassNewOrder(
                    $model::query()->ordered()->where('id', '!=', $model->id)->pluck('id')->toArray()
                );
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

    public function scopeOrdered(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy($this->determineOrderColumnName(), $direction);
    }

    public static function setNewOrder(
        $ids,
        int $startOrder = 1,
        string $primaryKeyColumn = null,
        callable $modifyQuery = null
    ): void {
        if (!is_array($ids) && !$ids instanceof ArrayAccess) {
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

    public static function setMassNewOrder(
        array $getSortables,
        int $incrementOrder = 1,
        ?string $primaryKeyColumn = null
    ): void {
        if (count($getSortables) === 0) {
            return;
        }

        $model = new static();
        $orderColumnName = $model->determineOrderColumnName();
        $ignoreTimestamps = config('eloquent-sortable.ignore_timestamps', false);

        if (is_null($primaryKeyColumn)) {
            $primaryKeyColumn = $model->getQualifiedKeyName();
        }

        $caseStatement = collect($getSortables)->reduce(function (string $carry, int $id) use (&$incrementOrder) {
            $incrementOrder++;
            $carry .= "WHEN {$id} THEN {$incrementOrder} ";
            return $carry;
        }, '');

        $getSortablesId = implode(', ', $getSortables);

        if ($ignoreTimestamps) {
            $model->timestamps = false;
        }

        DB::transaction(
            function () use (
                $model,
                $primaryKeyColumn,
                $orderColumnName,
                $caseStatement,
                $getSortablesId,
                $ignoreTimestamps
            ) {
                $updateQuery = "
            UPDATE {$model->getTable()}
            SET `{$orderColumnName}` = CASE {$primaryKeyColumn}
                {$caseStatement}
            END";

                if ($model->timestamps && Schema::hasColumn($model->getTable(), 'updated_at')) {
                    $consistentTimestamp = now();
                    $connection = DB::connection()->getDriverName();
                    $timestampUpdate = $connection === 'sqlite'
                        ? ", updated_at = '{$consistentTimestamp->format('Y-m-d H:i:s')}'"
                        : ", updated_at = '{$consistentTimestamp->toDateTimeString()}'";

                    $updateQuery .= $timestampUpdate;
                }

                $updateQuery .= " WHERE {$primaryKeyColumn} IN ({$getSortablesId})";

                DB::update($updateQuery);
            }
        );

        Event::dispatch(new EloquentModelSortedEvent(static::class));
    }

    public static function setNewOrderByCustomColumn(string $primaryKeyColumn, $ids, int $startOrder = 1): void
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

    public function shouldSortWhenUpdating(): bool
    {
        return $this->sortable['sort_when_updating'] ?? config('eloquent-sortable.sort_when_updating', true);
    }

    public function shouldSortWhenDeleting(): bool
    {
        return $this->sortable['sort_when_deleting'] ?? config('eloquent-sortable.sort_when_deleting', true);
    }

    public function moveOrderDown(): static
    {
        $orderColumnName = $this->determineOrderColumnName();

        $swapWithModel = $this->buildSortQuery()->limit(1)
            ->ordered()
            ->where($orderColumnName, '>', $this->$orderColumnName)
            ->first();

        if (!$swapWithModel) {
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

        if (!$swapWithModel) {
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
