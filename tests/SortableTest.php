<?php

declare(strict_types=1);

namespace Spatie\EloquentSortable\Test;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Spatie\EloquentSortable\EloquentModelSortedEvent;

class SortableTest extends TestCase
{
    /** @test */
    public function it_sets_the_order_column_on_creation()
    {
        foreach (Dummy::all() as $dummy) {
            $this->assertEquals($dummy->name, $dummy->order_column);
        }
    }

    /** @test */
    public function it_can_get_the_highest_order_number()
    {
        $this->assertEquals(Dummy::all()->count(), (new Dummy())->getHighestOrderNumber());
    }

    /** @test */
    public function it_can_get_the_highest_order_number_with_trashed_models()
    {
        $this->setUpSoftDeletes();

        DummyWithSoftDeletes::first()->delete();

        $this->assertEquals(
            DummyWithSoftDeletes::withTrashed()->count(),
            (new DummyWithSoftDeletes())->getHighestOrderNumber()
        );
    }

    /** @test */
    public function it_can_set_a_new_order()
    {
        Event::fake(EloquentModelSortedEvent::class);

        $newOrder = Collection::make(Dummy::all()->pluck('id'))->shuffle()->toArray();

        Dummy::setNewOrder($newOrder);

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }

        Event::assertDispatched(EloquentModelSortedEvent::class, function (EloquentModelSortedEvent $event) {
            return $event->isFor(Dummy::class);
        });
    }

    /** @test */
    public function it_can_touch_timestamps_when_setting_a_new_order()
    {
        $this->setUpTimestamps();
        DummyWithTimestamps::query()->update(['updated_at' => now()]);
        $originalTimestamps = DummyWithTimestamps::all()->pluck('updated_at');

        $this->travelTo(now()->addMinute());

        config()->set('eloquent-sortable.ignore_timestamps', false);
        $newOrder = Collection::make(DummyWithTimestamps::all()->pluck('id'))->shuffle()->toArray();
        DummyWithTimestamps::setNewOrder($newOrder);

        foreach (DummyWithTimestamps::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertNotEquals($originalTimestamps[$i], $dummy->updated_at);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_without_touching_timestamps()
    {
        $this->setUpTimestamps();
        DummyWithTimestamps::query()->update(['updated_at' => now()]);
        $originalTimestamps = DummyWithTimestamps::all()->pluck('updated_at');

        $this->travelTo(now()->addMinute());

        config()->set('eloquent-sortable.ignore_timestamps', true);
        $newOrder = Collection::make(DummyWithTimestamps::all()->pluck('id'))->shuffle()->toArray();
        DummyWithTimestamps::setNewOrder($newOrder);

        foreach (DummyWithTimestamps::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($originalTimestamps[$i], $dummy->updated_at);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_by_custom_column()
    {
        $newOrder = Collection::make(Dummy::all()->pluck('custom_column_sort'))->shuffle()->toArray();

        Dummy::setNewOrderByCustomColumn('custom_column_sort', $newOrder);

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->custom_column_sort);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_from_collection()
    {
        $newOrder = Collection::make(Dummy::all()->pluck('id'))->shuffle();

        Dummy::setNewOrder($newOrder);

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_by_custom_column_from_collection()
    {
        $newOrder = Collection::make(Dummy::all()->pluck('custom_column_sort'))->shuffle();

        Dummy::setNewOrderByCustomColumn('custom_column_sort', $newOrder);

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->custom_column_sort);
        }
    }

    /** @test */
    public function it_can_set_new_order_without_global_scopes_models()
    {
        $this->setUpIsActiveFieldForGlobalScope();

        $newOrder = Collection::make(Dummy::all()->pluck('id'))->shuffle()->toArray();

        DummyWithGlobalScope::setNewOrder($newOrder, 1, null, function ($query) {
            $query->withoutGlobalScope('ActiveScope');
        });

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_with_trashed_models()
    {
        $this->setUpSoftDeletes();

        $dummies = DummyWithSoftDeletes::all();

        $dummies->random()->delete();

        $newOrder = Collection::make($dummies->pluck('id'))->shuffle();

        DummyWithSoftDeletes::setNewOrder($newOrder);

        foreach (DummyWithSoftDeletes::withTrashed()->orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_by_custom_column_with_trashed_models()
    {
        $this->setUpSoftDeletes();

        $dummies = DummyWithSoftDeletes::all();

        $dummies->random()->delete();

        $newOrder = Collection::make($dummies->pluck('custom_column_sort'))->shuffle();

        DummyWithSoftDeletes::setNewOrderByCustomColumn('custom_column_sort', $newOrder);

        foreach (DummyWithSoftDeletes::withTrashed()->orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->custom_column_sort);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_without_trashed_models()
    {
        $this->setUpSoftDeletes();

        DummyWithSoftDeletes::first()->delete();

        $newOrder = Collection::make(DummyWithSoftDeletes::pluck('id'))->shuffle();

        DummyWithSoftDeletes::setNewOrder($newOrder);

        foreach (DummyWithSoftDeletes::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_can_set_a_new_order_by_custom_column_without_trashed_models()
    {
        $this->setUpSoftDeletes();

        DummyWithSoftDeletes::first()->delete();

        $newOrder = Collection::make(DummyWithSoftDeletes::pluck('custom_column_sort'))->shuffle();

        DummyWithSoftDeletes::setNewOrderByCustomColumn('custom_column_sort', $newOrder);

        foreach (DummyWithSoftDeletes::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->custom_column_sort);
        }
    }

    /** @test */
    public function it_will_determine_to_sort_when_creating_if_sortable_attribute_does_not_exist()
    {
        $model = new Dummy();

        $this->assertTrue($model->shouldSortWhenCreating());
    }

    /** @test */
    public function it_will_determine_to_sort_when_creating_if_sort_when_creating_setting_does_not_exist()
    {
        $model = new class () extends Dummy {
            public $sortable = [];
        };

        $this->assertTrue($model->shouldSortWhenCreating());
    }

    /** @test */
    public function it_will_respect_the_sort_when_creating_setting()
    {
        $model = new class () extends Dummy {
            public $sortable = ['sort_when_creating' => true];
        };

        $this->assertTrue($model->shouldSortWhenCreating());

        $model = new class () extends Dummy {
            public $sortable = ['sort_when_creating' => false];
        };
        $this->assertFalse($model->shouldSortWhenCreating());
    }

    /** @test */
    public function it_provides_an_ordered_trait()
    {
        $i = 1;

        foreach (Dummy::ordered()->get()->pluck('order_column') as $order) {
            $this->assertEquals($i++, $order);
        }
    }

    /** @test */
    public function it_can_move_the_order_down()
    {
        $firstModel = Dummy::find(3);
        $secondModel = Dummy::find(4);

        $this->assertEquals($firstModel->order_column, 3);
        $this->assertEquals($secondModel->order_column, 4);

        $this->assertNotFalse($firstModel->moveOrderDown());

        $firstModel = Dummy::find(3);
        $secondModel = Dummy::find(4);

        $this->assertEquals($firstModel->order_column, 4);
        $this->assertEquals($secondModel->order_column, 3);
    }

    /** @test */
    public function it_will_not_fail_when_it_cant_move_the_order_down()
    {
        $lastModel = Dummy::all()->last();

        $this->assertEquals($lastModel->order_column, 20);
        $this->assertEquals($lastModel, $lastModel->moveOrderDown());
    }

    /** @test */
    public function it_can_move_the_order_up()
    {
        $firstModel = Dummy::find(3);
        $secondModel = Dummy::find(4);

        $this->assertEquals($firstModel->order_column, 3);
        $this->assertEquals($secondModel->order_column, 4);

        $this->assertNotFalse($secondModel->moveOrderUp());

        $firstModel = Dummy::find(3);
        $secondModel = Dummy::find(4);

        $this->assertEquals($firstModel->order_column, 4);
        $this->assertEquals($secondModel->order_column, 3);
    }

    /** @test */
    public function it_will_not_break_when_it_cant_move_the_order_up()
    {
        $lastModel = Dummy::first();

        $this->assertEquals($lastModel->order_column, 1);
        $this->assertEquals($lastModel, $lastModel->moveOrderUp());
    }

    /** @test */
    public function it_can_swap_the_position_of_two_given_models()
    {
        $firstModel = Dummy::find(3);
        $secondModel = Dummy::find(4);

        $this->assertEquals($firstModel->order_column, 3);
        $this->assertEquals($secondModel->order_column, 4);

        Dummy::swapOrder($firstModel, $secondModel);

        $this->assertEquals($firstModel->order_column, 4);
        $this->assertEquals($secondModel->order_column, 3);
    }

    /** @test */
    public function it_can_swap_itself_with_another_model()
    {
        $firstModel = Dummy::find(3);
        $secondModel = Dummy::find(4);

        $this->assertEquals($firstModel->order_column, 3);
        $this->assertEquals($secondModel->order_column, 4);

        $firstModel->swapOrderWithModel($secondModel);

        $this->assertEquals($firstModel->order_column, 4);
        $this->assertEquals($secondModel->order_column, 3);
    }

    /** @test */
    public function it_can_move_a_model_to_the_first_place()
    {
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
        }
    }

    /**
     * @test
     */
    public function it_can_move_a_model_to_the_last_place()
    {
        $position = 3;

        $oldModels = Dummy::whereNot('id', $position)->get();

        $model = Dummy::find($position);

        $this->assertNotEquals(20, $model->order_column);

        $model = $model->moveToEnd();

        $this->assertEquals(20, $model->order_column);

        $oldModels = $oldModels->pluck('order_column', 'id');

        $newModels = Dummy::whereNot('id', $position)->get()->pluck('order_column', 'id');

        foreach ($oldModels as $key => $order) {
            if ($order > $position) {
                $this->assertEquals($order - 1, $newModels[$key]);
            } else {
                $this->assertEquals($order, $newModels[$key]);
            }
        }
    }

    /** @test */
    public function it_can_use_config_properties()
    {
        config([
            'eloquent-sortable.order_column_name' => 'order_column',
            'eloquent-sortable.sort_when_creating' => true,
        ]);

        $model = new class () extends Dummy {
            public $sortable = [];
        };

        $this->assertEquals(config('eloquent-sortable.order_column_name'), $model->determineOrderColumnName());
        $this->assertEquals(config('eloquent-sortable.sort_when_creating'), $model->shouldSortWhenCreating());
    }

    /** @test */
    public function it_can_override_config_properties()
    {
        $model = new class () extends Dummy {
            public $sortable = [
                'order_column_name' => 'my_custom_order_column',
                'sort_when_creating' => false,
            ];
        };

        $this->assertEquals($model->determineOrderColumnName(), 'my_custom_order_column');
        $this->assertFalse($model->shouldSortWhenCreating());
    }

    /** @test */
    public function it_can_tell_if_element_is_first_in_order()
    {
        $model = (new Dummy())->buildSortQuery()->get();
        $this->assertTrue($model[0]->isFirstInOrder());
        $this->assertFalse($model[1]->isFirstInOrder());
    }

    /** @test */
    public function it_can_tell_if_element_is_last_in_order()
    {
        $model = (new Dummy())->buildSortQuery()->get();
        $this->assertTrue($model[$model->count() - 1]->isLastInOrder());
        $this->assertFalse($model[$model->count() - 2]->isLastInOrder());
    }

    /** @test */
    public function it_sets_mass_new_order_correctly()
    {
        $newOrder = Collection::make(Dummy::all()->pluck('id'))->shuffle()->toArray();

        Dummy::setMassNewOrder($newOrder);

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_updates_order_when_sortables_property_is_set()
    {
        // Shuffle order and set it on the model as sortables
        $newOrder = Dummy::pluck('id')->shuffle()->toArray(); // Get IDs and shuffle them

        $model = Dummy::first();
        $model->sortables = $newOrder; // Assuming this property is used for ordering
        $model->save();

        // Create CASE statement to order by the shuffled IDs
        $orderByClause = "CASE id ";
        foreach ($newOrder as $index => $id) {
            $orderByClause .= "WHEN {$id} THEN {$index} ";
        }
        $orderByClause .= "END";

        // Retrieve the dummies in the shuffled order using CASE statement
        $dummies = Dummy::whereIn('id', $newOrder)
            ->orderByRaw($orderByClause)
            ->get();

        // Verify that the new order matches the expected order
        foreach ($dummies as $index => $dummy) {
            $this->assertEquals($newOrder[$index], $dummy->id);
        }
    }

    /** @test */
    public function it_does_not_update_order_when_sortables_is_not_set_on_update()
    {
        // Get the first model
        $model = Dummy::first();

        // Get the original order
        $originalOrder = Dummy::orderBy('order_column')->pluck('id')->toArray(); // Ensure order is consistent

        // Update the model without changing the sortables
        $model->name = 'Updated Name';
        $model->save();

        // Retrieve models in the current order and compare with the original
        $currentOrder = Dummy::orderBy('order_column')->pluck('id')->toArray();

        // Verify that the order has not changed
        foreach ($originalOrder as $i => $id) {
            $this->assertEquals(
                $id,
                $currentOrder[$i],
                "Order mismatch at index {$i}. Expected {$id}, got {$currentOrder[$i]}"
            );
        }
    }

    /** @test */
    public function it_updates_order_when_sortables_property_is_set_on_delete()
    {
        $modelToDelete = Dummy::first();
        $remainingModels = Dummy::where('id', '!=', $modelToDelete->id)->pluck('id');

        $newOrder = $remainingModels->shuffle()->toArray();
        $modelToDelete->sortables = $newOrder;

        $modelToDelete->delete();

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($newOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_does_not_update_order_when_sortables_is_not_set_on_delete()
    {
        $modelToDelete = Dummy::first();
        $remainingModels = Dummy::where('id', '!=', $modelToDelete->id)->pluck('id');

        $originalOrder = $remainingModels->values()->toArray();

        // Do not provide sortables to the model before deleting
        $modelToDelete->delete();

        foreach (Dummy::orderBy('order_column')->get() as $i => $dummy) {
            $this->assertEquals($originalOrder[$i], $dummy->id);
        }
    }

    /** @test */
    public function it_dispatches_sorted_event_on_mass_update_for_sortables()
    {
        Event::fake(EloquentModelSortedEvent::class);

        $newOrder = Collection::make(Dummy::all()->pluck('id'))->shuffle()->toArray();
        Dummy::setMassNewOrder($newOrder);

        Event::assertDispatched(EloquentModelSortedEvent::class, function (EloquentModelSortedEvent $event) {
            return $event->isFor(Dummy::class);
        });
    }

    /** @test */
    public function it_respects_ignore_timestamps_on_mass_update_for_sortables()
    {
        // Set up a consistent timestamp
        $consistentTimestamp = now();

        // Set up timestamps on the models using the consistent timestamp
        $this->setUpTimestamps();
        DummyWithTimestamps::query()->update(['updated_at' => $consistentTimestamp]);

        // Pluck the original timestamps to use for comparison
        $originalTimestamps = DummyWithTimestamps::all()->pluck('updated_at');

        // Move forward in time by one minute for the next round of updates
        $this->travelTo($consistentTimestamp->copy()->addMinute());

        // Update with timestamps enabled
        config()->set('eloquent-sortable.ignore_timestamps', false);
        $this->assertFalse(config('eloquent-sortable.ignore_timestamps'), 'ignore_timestamps should be false');

        $newOrder = Collection::make(DummyWithTimestamps::all()->pluck('id'))->shuffle()->toArray();
        DummyWithTimestamps::setMassNewOrder($newOrder);

        // Verify that the timestamps have been updated
        $dummies = DummyWithTimestamps::orderBy('order_column')->get();

        foreach ($dummies as $i => $dummy) {
            $this->assertNotEquals(
                $originalTimestamps[$i],
                $dummy->updated_at,
                "Timestamps should have been updated, but they were not. Index: {$i}"
            );
        }

        $dummyWithTimestamps = new DummyWithTimestamps();
        $dummyWithTimestamps->timestamps = false;
        $dummyWithTimestamps::setMassNewOrder($newOrder);
        $dummyWithTimestamps->refresh();

        // Move forward in time by another minute for the next round of updates
        $this->travelTo($consistentTimestamp->copy()->addMinutes());

        // Update with timestamps disabled
        config()->set('eloquent-sortable.ignore_timestamps', true);
        $this->assertTrue(config('eloquent-sortable.ignore_timestamps'), 'ignore_timestamps should be true');

        // Verify that the timestamps have not changed
        $currentTimestamps = $dummyWithTimestamps::orderBy('order_column')->pluck('updated_at')->toArray();

        foreach ($dummyWithTimestamps->all()->pluck('updated_at') as $i => $timestamp) {
            $this->assertEquals(
                $timestamp,
                $currentTimestamps[$i],
                "Timestamps should not have been updated, but they were. Index: {$i}"
            );
        }
    }

    /** @test */
    public function it_respects_sort_when_updating_setting()
    {
        $model = new class () extends Dummy {
            public $sortable = ['sort_when_updating' => true];
        };

        $this->assertTrue($model->shouldSortWhenUpdating());

        $model = new class () extends Dummy {
            public $sortable = ['sort_when_updating' => false];
        };

        $this->assertFalse($model->shouldSortWhenUpdating());
    }

    /** @test */
    public function it_respects_sort_when_deleting_setting()
    {
        $model = new class () extends Dummy {
            public $sortable = ['sort_when_deleting' => true];
        };

        $this->assertTrue($model->shouldSortWhenDeleting());

        $model = new class () extends Dummy {
            public $sortable = ['sort_when_deleting' => false];
        };

        $this->assertFalse($model->shouldSortWhenDeleting());
    }
}
