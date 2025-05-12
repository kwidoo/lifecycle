<?php

namespace Kwidoo\Lifecycle\CQRS\Commands;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\CQRS\Contracts\Command;
use ReflectionClass;

/**
 * Factory for creating Command DTOs from LifecycleContextData
 */
class CommandFactory
{
    /**
     * Create a new command factory
     *
     * @param Container $container
     * @param array $cqrsMappings CQRS mappings from config
     */
    public function __construct(
        protected Container $container,
        protected array $cqrsMappings = []
    ) {}

    /**
     * Create a command from lifecycle data
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return Command
     * @throws InvalidArgumentException If the command mapping is not found
     */
    public function createFromLifecycleData(LifecycleContextData|LifecycleData $data): Command
    {
        $action = $data->action;
        $resource = $data->resource;
        $context = $data->context;

        // Generate key for mapping lookup
        $mappingKey = "{$resource}.{$action}";

        // Check if we have a mapping for this action.resource pair
        if (!isset($this->cqrsMappings[$mappingKey])) {
            throw new InvalidArgumentException("No CQRS mapping found for {$mappingKey}");
        }

        $mapping = $this->cqrsMappings[$mappingKey];

        // If a command factory is specified in the mapping, use it
        if (isset($mapping['command_factory'])) {
            $factory = $mapping['command_factory'];

            // If it's a callable array [class, method], resolve and call it
            if (is_array($factory) && count($factory) === 2) {
                [$class, $method] = $factory;
                $instance = $this->container->make($class);
                return $instance->$method($data);
            }

            // If it's a callable, just call it
            if (is_callable($factory)) {
                return $factory($data);
            }
        }

        // Otherwise, create command from the mapping
        $commandClass = $mapping['command'];

        // Resolve the aggregate ID using the uuid_resolver in the mapping
        $aggregateId = isset($mapping['uuid_resolver']) && is_callable($mapping['uuid_resolver'])
            ? $mapping['uuid_resolver']($context)
            : ($context['id'] ?? Str::uuid()->toString());

        // Create an instance of the command with the aggregate ID and context data
        return $this->createCommand($commandClass, $aggregateId, $context);
    }

    /**
     * Create a command instance
     *
     * @param string $commandClass
     * @param string|int $aggregateId
     * @param array $context
     * @return Command
     */
    protected function createCommand(string $commandClass, string|int $aggregateId, array $context): Command
    {
        $reflection = new ReflectionClass($commandClass);
        $constructor = $reflection->getConstructor();

        // If no constructor, just create an instance and set the aggregate ID
        if (!$constructor) {
            $command = new $commandClass();
            if (method_exists($command, 'setAggregateId')) {
                $command->setAggregateId($aggregateId);
            }
            return $command;
        }

        // Get constructor parameters
        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $paramName = $parameter->getName();

            // If the parameter is aggregateId, use our resolved value
            if ($paramName === 'aggregateId' || $paramName === 'id' || $paramName === 'uuid') {
                $parameters[] = $aggregateId;
                continue;
            }

            // For other parameters, look them up in the context
            if (array_key_exists($paramName, $context)) {
                $parameters[] = $context[$paramName];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $parameters[] = $parameter->getDefaultValue();
            } else {
                // If required parameter is missing, throw an exception
                throw new InvalidArgumentException(
                    "Missing required parameter {$paramName} for command {$commandClass}"
                );
            }
        }

        return new $commandClass(...$parameters);
    }
}
