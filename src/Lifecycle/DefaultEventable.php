<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Illuminate\Contracts\Events\Dispatcher;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Eventable;

class DefaultEventable implements Eventable
{
    public function __construct(protected Dispatcher $dispatcher) {}

    /**
     * @param string
     * @param mixed
     * @return void
     */
    public function dispatch(string $eventKey, mixed $context)
    {
        $this->dispatcher->dispatch($eventKey, ['data' => $context]);
    }
}
//
