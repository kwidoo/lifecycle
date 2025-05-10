<?php

namespace Kwidoo\Lifecycle\Features\Event;

class EventKeyBuilder
{
    /**
     * Build a key for before events
     *
     * @param string $action
     * @param string $resource
     * @return string
     */
    public function buildBeforeKey(string $action, string $resource): string
    {
        return $this->buildKey('before', $action, $resource);
    }

    /**
     * Build a key for after events
     *
     * @param string $action
     * @param string $resource
     * @return string
     */
    public function buildAfterKey(string $action, string $resource): string
    {
        return $this->buildKey('after', $action, $resource);
    }

    /**
     * Build a key for error events
     *
     * @param string $action
     * @param string $resource
     * @return string
     */
    public function buildErrorKey(string $action, string $resource): string
    {
        return $this->buildKey('error', $action, $resource);
    }

    /**
     * Build a consistent event key in the format 'phase.resource.action'
     *
     * @param string $phase
     * @param string $action
     * @param string $resource
     * @return string
     */
    protected function buildKey(string $phase, string $action, string $resource): string
    {
        return "$phase.$resource.$action";
    }
}
