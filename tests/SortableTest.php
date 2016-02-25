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

    /**
     * @test
     */
    public function it_provides_an_ordered_trait()
    {
        $i = 1;

        foreach (Dummy::ordered()->get()->lists('order_column') as $order) {
            $this->assertEquals($i++, $order);
        };
    }

    /**
     * @test
     */
    public function it_can_move_the_order_down()
    {
        $firstModel = Dummy::find(1);
        $secondModel = Dummy::find(2);

        $this->assertEquals($firstModel->order_column, 1);
        $this->assertEquals($secondModel->order_column, 2);

        $this->assertNotFalse($firstModel->moveOrderDown());

        $firstModel = Dummy::find(1);
        $secondModel = Dummy::find(2);

        $this->assertEquals($firstModel->order_column, 2);
        $this->assertEquals($secondModel->order_column, 1);
    }

    /**
     * @test
     */
    public function it_will_not_fail_when_it_cant_move_the_order_down()
    {
        $lastModel = Dummy::all()->last();

        $this->assertEquals($lastModel->order_column, 20);
        $this->assertEquals($lastModel, $lastModel->moveOrderDown());
    }

    /**
     * @test
     */
    public function it_can_move_the_order_up()
    {
        $firstModel = Dummy::find(1);
        $secondModel = Dummy::find(2);

        $this->assertEquals($firstModel->order_column, 1);
        $this->assertEquals($secondModel->order_column, 2);

        $this->assertNotFalse($secondModel->moveOrderUp());

        $firstModel = Dummy::find(1);
        $secondModel = Dummy::find(2);

        $this->assertEquals($firstModel->order_column, 2);
        $this->assertEquals($secondModel->order_column, 1);
    }

    /**
     * @test
     */
    public function it_will_not_when_it_cant_move_the_order_up()
    {
        $lastModel = Dummy::first();

        $this->assertEquals($lastModel->order_column, 1);
        $this->assertEquals($lastModel, $lastModel->moveOrderUp());
    }
}
