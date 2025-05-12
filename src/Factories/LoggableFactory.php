<?php

namespace Kwidoo\Lifecycle\Factories;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\App;
use Kwidoo\Lifecycle\Contracts\Features\Loggable;
use Kwidoo\Lifecycle\Features\Log\DefaultLoggable;

class LoggableFactory
{
    /**
     * Factory registry
     *
     * @var array
     */
    protected static array $registry = [];

    public function __construct(
        protected Container $container
    ) {}

    /**
     * Register a loggable implementation for a specific domain
     *
     * @param string $domain
     * @param string $implementation
     * @return void
     */
    public static function register(string $domain, string $implementation): void
    {
        if (!is_subclass_of($implementation, Loggable::class)) {
            throw new \InvalidArgumentException("Implementation must implement Loggable interface");
        }

        static::$registry[$domain] = $implementation;
    }

    /**
     * Resolve loggable implementation based on domain context
     *
     * @param string|null $domain
     * @param array $params
     * @return Loggable
     */
    public function resolve(?string $domain = null, array $params = []): Loggable
    {
        // If no specific domain provided, use the default one from config
        if ($domain === null) {
            $domain = config('lifecycle.default_logger_domain', 'default');
        }

        // If domain registered, resolve it from the service container
        if (isset(static::$registry[$domain])) {
            $implementation = static::$registry[$domain];
            return $this->container->make($implementation, $params);
        }

        // Otherwise return default implementation
        return $this->container->make(DefaultLoggable::class, $params);
    }
}
