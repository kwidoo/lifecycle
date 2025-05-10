<?php

namespace Kwidoo\Lifecycle\Features\Log;

class LogKeyBuilder
{
    /**
     * Build a key for before logging
     *
     * @param string $action
     * @param string $resource
     * @return string
     */
    public function buildBeforeKey(string $action, string $resource): string
    {
        return $this->buildKey('start', $action, $resource);
    }

    /**
     * Build a key for after logging
     *
     * @param string $action
     * @param string $resource
     * @return string
     */
    public function buildAfterKey(string $action, string $resource): string
    {
        return $this->buildKey('complete', $action, $resource);
    }

    /**
     * Build a key for error logging
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
     * Build a consistent log key
     *
     * @param string $phase
     * @param string $action
     * @param string $resource
     * @return string
     */
    protected function buildKey(string $phase, string $action, string $resource): string
    {
        return "Lifecycle {$phase}: {$resource}.{$action}";
    }
}
