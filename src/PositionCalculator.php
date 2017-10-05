<?php

namespace Spatie\EloquentSortable;

class PositionCalculator
{
    public function __invoke($isMovedAfter, $isMovingForward, $postion)
    {
        if ($isMovedAfter) {
            ++$postion;
        }

        if ($isMovingForward) {
            --$postion;
        }

        return $postion;
    }
}
