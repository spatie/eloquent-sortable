<?php

namespace Spatie\EloquentSortable;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SortedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(protected Model $instance)
    {
    }
}
