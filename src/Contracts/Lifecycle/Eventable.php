<?php

namespace Kwidoo\Lifecycle\Contracts\Lifecycle;

interface Eventable
{
    /**
     * @param string $eventKey
     * @param mixed $context
     *
     * @return [type]
     */
    public function dispatch(string $eventKey, mixed $context);
}
