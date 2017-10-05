<?php

namespace Spatie\EloquentSortable\Test;

use Spatie\EloquentSortable\PositionCalculator;

class PositionCalcualtorTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function it_calculates_the_new_positon_moving_from_position_inferior_after_the_target()
    {
        $calculator = new PositionCalculator;

        $newPosition = $calculator(true, true, 2);

        $this->assertEquals(2, $newPosition);
    }

    /** @test */
    public function it_calculates_the_new_positon_moving_from_position_superior_after_the_target()
    {
        $calculator = new PositionCalculator;

        $newPosition = $calculator(true, false, 2);

        $this->assertEquals(3, $newPosition);
    }

    /** @test */
    public function it_calculates_the_new_positon_moving_from_position_inferior_before_the_target()
    {
        $calculator = new PositionCalculator;

        $newPosition = $calculator(false, true, 2);

        $this->assertEquals(1, $newPosition);
    }

    /** @test */
    public function it_calculates_the_new_positon_moving_from_position_superior_before_the_target()
    {
        $calculator = new PositionCalculator;

        $newPosition = $calculator(false, false, 2);

        $this->assertEquals(2, $newPosition);
    }
}
