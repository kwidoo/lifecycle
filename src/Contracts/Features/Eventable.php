<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

interface Eventable
{
    /**
     * Dispatch an event with the given key and payload
     *
     * @param string $eventKey
     * @param mixed $payload
     * @return void
     */
    public function dispatch(string $eventKey, mixed $payload): void;
}
