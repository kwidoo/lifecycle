<?php

namespace Kwidoo\Lifecycle\Contracts\Factories;

use Kwidoo\Lifecycle\Contracts\Features\Loggable;

interface LoggableFactory
{
    /**
     * Register a loggable implementation for a specific domain
     *
     * @param string $domain
     * @param string $implementation
     * @return void
     */
    public static function register(string $domain, string $implementation): void;

    /**
     * Resolve loggable implementation based on domain context
     *
     * @param string|null $domain
     * @param array $params
     * @return Loggable
     */
    public function resolve(?string $domain = null, array $params = []): Loggable;
}
