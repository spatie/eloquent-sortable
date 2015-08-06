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
        $this->assertEquals(Dummy::all()->count(), (new Dummy())->getHighestOrderNumber());
    }

    /**
     * @test
     */
    public function it_can_set_a_new_order()
    {
        $newOrder = Collection::make(Dummy::all()->lists('id'))->shuffle()->toArray();

        Dummy::setNewOrder($newOrder);

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }
    }

    /**
     * @test
     */
    public function it_will_determine_to_sort_when_creating_if_sortable_attribute_does_not_exist()
    {
        $model = new Dummy();

        $this->assertTrue($model->shouldSortWhenCreating());
    }

    /**
     * @test
     */
    public function it_will_determine_to_sort_when_creating_if_sort_when_creating_setting_does_not_exist()
    {
        $model = new DummyWithSortableSetting();

        $this->assertTrue($model->shouldSortWhenCreating());
    }

    /**
     * @test
     */
    public function it_will_respect_the_sort_when_creating_setting()
    {
        $model = new DummyWithSortableSetting();

        $model->sortable['sort_when_creating'] = true;
        $this->assertTrue($model->shouldSortWhenCreating());

        $model->sortable['sort_when_creating'] = false;
        $this->assertFalse($model->shouldSortWhenCreating());
    }
}
