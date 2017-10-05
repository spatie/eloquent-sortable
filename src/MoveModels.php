<?php

namespace Spatie\EloquentSortable;

class MoveModels
{

    private $orderColumnName;

    public function __construct($orderColumnName)
    {
        $this->orderColumnName = $orderColumnName;
    }

    public function __invoke($action, $moved, $displaced)
    {
        $oldPosition = $moved->{$this->orderColumnName};
        $newPosition = $displaced->{$this->orderColumnName};

        if ($oldPosition === $newPosition) {
            return;
        }

        $positionCalculator = new PositionCalculator;
        $movedAfter = $action === 'moveAfter';
        $movingForward = $oldPosition < $newPosition;
        $method = $movingForward ? 'decrement' : 'increment';


        $moved->buildSortQuery()
            ->where($this->orderColumnName, '>', min([$oldPosition, $newPosition]))
            ->where($this->orderColumnName, '<', max([$oldPosition, $newPosition]))
            ->$method($this->orderColumnName);


        $moved->{$this->orderColumnName} = $positionCalculator($movedAfter, $movingForward, $newPosition);
        $displaced->{$this->orderColumnName} = $positionCalculator(!$movedAfter, $movingForward, $newPosition);

        $moved->save();
        $displaced->save();
    }
}
