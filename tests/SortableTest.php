<?php

namespace Spatie\EloquentSortable\Test;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Spatie\EloquentSortable\EloquentModelSortedEvent;
class SortableTest extends TestCase
{
        #[Test]
    public function it_sets_the_order_column_on_creation()
    {
        foreach (Dummy::all() as $dummy) {
            $this->assertEquals($dummy->name, $dummy->order_column);
        }
    }
    public function it_can_get_the_highest_order_number()
        $this->assertEquals(Dummy::all()->count(), (new Dummy())->getHighestOrderNumber());
    public function it_can_get_the_highest_order_number_with_trashed_models()
        $this->setUpSoftDeletes();
        DummyWithSoftDeletes::first()->delete();
        $this->assertEquals(DummyWithSoftDeletes::withTrashed()->count(), (new DummyWithSoftDeletes())->getHighestOrderNumber());
    public function it_can_set_a_new_order()
        Event::fake(EloquentModelSortedEvent::class);
        $newOrder = Collection::make(Dummy::all()->pluck('id'))->shuffle()->toArray();
        Dummy::setNewOrder($newOrder);
        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        Event::assertDispatched(EloquentModelSortedEvent::class, function (EloquentModelSortedEvent $event) {
            return $event->isFor(Dummy::class);
        });
    public function it_can_touch_timestamps_when_setting_a_new_order()
        $this->setUpTimestamps();
        DummyWithTimestamps::query()->update(['updated_at' => now()]);
        $originalTimestamps = DummyWithTimestamps::all()->pluck('updated_at');
        $this->travelTo(now()->addMinute());
        config()->set('eloquent-sortable.ignore_timestamps', false);
        $newOrder = Collection::make(DummyWithTimestamps::all()->pluck('id'))->shuffle()->toArray();
        DummyWithTimestamps::setNewOrder($newOrder);
        foreach (DummyWithTimestamps::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertNotEquals($originalTimestamps[$i], $dummy->updated_at);
    public function it_can_set_a_new_order_without_touching_timestamps()
        config()->set('eloquent-sortable.ignore_timestamps', true);
            $this->assertEquals($originalTimestamps[$i], $dummy->updated_at);
    public function it_can_set_a_new_order_by_custom_column()
        $newOrder = Collection::make(Dummy::all()->pluck('custom_column_sort'))->shuffle()->toArray();
        Dummy::setNewOrderByCustomColumn('custom_column_sort', $newOrder);
            $this->assertEquals($newOrder[$i], $dummy->custom_column_sort);
    public function it_can_set_a_new_order_from_collection()
        $newOrder = Collection::make(Dummy::all()->pluck('id'))->shuffle();
    public function it_can_set_a_new_order_by_custom_column_from_collection()
        $newOrder = Collection::make(Dummy::all()->pluck('custom_column_sort'))->shuffle();
    public function it_can_set_new_order_without_global_scopes_models()
        $this->setUpIsActiveFieldForGlobalScope();
        DummyWithGlobalScope::setNewOrder($newOrder, 1, null, function ($query) {
            $query->withoutGlobalScope('ActiveScope');
    public function it_can_set_a_new_order_with_trashed_models()
        $dummies = DummyWithSoftDeletes::all();
        $dummies->random()->delete();
        $newOrder = Collection::make($dummies->pluck('id'))->shuffle();
        DummyWithSoftDeletes::setNewOrder($newOrder);
        foreach (DummyWithSoftDeletes::withTrashed()->orderBy('order_column')->get() as $i => $dummy) {
    public function it_can_set_a_new_order_by_custom_column_with_trashed_models()
        $newOrder = Collection::make($dummies->pluck('custom_column_sort'))->shuffle();
        DummyWithSoftDeletes::setNewOrderByCustomColumn('custom_column_sort', $newOrder);
    public function it_can_set_a_new_order_without_trashed_models()
        $newOrder = Collection::make(DummyWithSoftDeletes::pluck('id'))->shuffle();
        foreach (DummyWithSoftDeletes::orderBy('order_column')->get() as $i => $dummy) {
    public function it_can_set_a_new_order_by_custom_column_without_trashed_models()
        $newOrder = Collection::make(DummyWithSoftDeletes::pluck('custom_column_sort'))->shuffle();
    public function it_will_determine_to_sort_when_creating_if_sortable_attribute_does_not_exist()
        $model = new Dummy();
        $this->assertTrue($model->shouldSortWhenCreating());
    public function it_will_determine_to_sort_when_creating_if_sort_when_creating_setting_does_not_exist()
        $model = new class () extends Dummy {
            public $sortable = [];
        };
    public function it_will_respect_the_sort_when_creating_setting()
            public $sortable = ['sort_when_creating' => true];
            public $sortable = ['sort_when_creating' => false];
        $this->assertFalse($model->shouldSortWhenCreating());
    public function it_provides_an_ordered_trait()
        $i = 1;
        foreach (Dummy::ordered()->get()->pluck('order_column') as $order) {
            $this->assertEquals($i++, $order);
    public function it_can_move_after()
        $firstModel = Dummy::find(3);
        $secondModel = Dummy::find(4);
        $newModel = Dummy::create(['name' => 'New dummy', 'custom_column_sort' => rand()]);
        $newModel->moveAfter($firstModel);
        $newModel->refresh();
        $secondModel->refresh();
        $this->assertEquals($newModel->order_column, 4);
        $this->assertEquals($secondModel->order_column, 5);
    public function it_can_move_before()
        $firstModel = Dummy::find(4);
        $newModel->moveBefore($firstModel);
        $firstModel->refresh();
        $this->assertEquals($firstModel->order_column, 5);
    public function it_can_move_the_order_down()
        $this->assertEquals($firstModel->order_column, 3);
        $this->assertEquals($secondModel->order_column, 4);
        $this->assertNotFalse($firstModel->moveOrderDown());
        $this->assertEquals($firstModel->order_column, 4);
        $this->assertEquals($secondModel->order_column, 3);
    public function it_will_not_fail_when_it_cant_move_the_order_down()
        $lastModel = Dummy::all()->last();
        $this->assertEquals($lastModel->order_column, 20);
        $this->assertEquals($lastModel, $lastModel->moveOrderDown());
    public function it_can_move_the_order_up()
        $this->assertNotFalse($secondModel->moveOrderUp());
    public function it_will_not_break_when_it_cant_move_the_order_up()
        $lastModel = Dummy::first();
        $this->assertEquals($lastModel->order_column, 1);
        $this->assertEquals($lastModel, $lastModel->moveOrderUp());
    public function it_can_swap_the_position_of_two_given_models()
        Dummy::swapOrder($firstModel, $secondModel);
    public function it_can_swap_itself_with_another_model()
        $firstModel->swapOrderWithModel($secondModel);
    public function it_can_move_a_model_to_the_first_place()
        $position = 3;
        $oldModels = Dummy::whereNot('id', $position)->get();
        $model = Dummy::find($position);
        $this->assertEquals(3, $model->order_column);
        $model = $model->moveToStart();
        $this->assertEquals(1, $model->order_column);
        $oldModels = $oldModels->pluck('order_column', 'id');
        $newModels = Dummy::whereNot('id', $position)->get()->pluck('order_column', 'id');
        foreach ($oldModels as $key => $oldModel) {
            $this->assertEquals($oldModel + 1, $newModels[$key]);
    /**
     * @test
     */
    public function it_can_move_a_model_to_the_last_place()
        $this->assertNotEquals(20, $model->order_column);
        $model = $model->moveToEnd();
        $this->assertEquals(20, $model->order_column);
        foreach ($oldModels as $key => $order) {
            if ($order > $position) {
                $this->assertEquals($order - 1, $newModels[$key]);
            } else {
                $this->assertEquals($order, $newModels[$key]);
            }
    public function it_can_use_config_properties()
        config([
        'eloquent-sortable.order_column_name' => 'order_column',
        'eloquent-sortable.sort_when_creating' => true,
      ]);
        $this->assertEquals(config('eloquent-sortable.order_column_name'), $model->determineOrderColumnName());
        $this->assertEquals(config('eloquent-sortable.sort_when_creating'), $model->shouldSortWhenCreating());
    public function it_can_override_config_properties()
            public $sortable = [
            'order_column_name' => 'my_custom_order_column',
            'sort_when_creating' => false,
          ];
        $this->assertEquals($model->determineOrderColumnName(), 'my_custom_order_column');
    public function it_can_tell_if_element_is_first_in_order()
        $model = (new Dummy())->buildSortQuery()->get();
        $this->assertTrue($model[0]->isFirstInOrder());
        $this->assertFalse($model[1]->isFirstInOrder());
    public function it_can_tell_if_element_is_last_in_order()
        $this->assertTrue($model[$model->count() - 1]->isLastInOrder());
        $this->assertFalse($model[$model->count() - 2]->isLastInOrder());
}
