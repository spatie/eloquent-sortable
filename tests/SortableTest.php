<?php

namespace Spatie\EloquentSortable\Test;

use Illuminate\Support\Collection;

class SortableTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_the_order_column_on_creation()
    {
        foreach (Dummy::all() as $dummy) {
            $this->assertEquals($dummy->name, $dummy->order_column);
        }
    }

    /**
     * @test
     */
    public function it_can_get_the_highest_order_number()
    {
        $this->assertEquals(Dummy::all()->count(), (new Dummy)->getHighestOrderNumber());
    }

    /**
     * @test
     */
    public function it_can_set_a_new_order()
    {
        $newOrder = Collection::make(Dummy::all()->pluck('id'))->shuffle()->toArray();

        Dummy::setNewOrder($newOrder);

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }
    }
}
