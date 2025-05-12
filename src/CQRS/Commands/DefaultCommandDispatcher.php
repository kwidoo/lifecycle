<?php

namespace Kwidoo\Lifecycle\CQRS\Commands;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\CQRS\Contracts\Command;
use Kwidoo\Lifecycle\CQRS\Contracts\CommandDispatcher;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\AggregateRoots\AggregateRootRepository;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Default implementation of CommandDispatcher
 *
 * This dispatcher maps commands to aggregate handlers using naming conventions
 * and configuration from config/lifecycle.php.
 */
class DefaultCommandDispatcher implements CommandDispatcher
{
    /**
     * Map of command class names to aggregate class and handler method
     *
     * @var array<string, array{aggregate: string, method: string}>
     */
    protected array $commandHandlerMap = [];

    /**
     * Create a new command dispatcher
     *
     * @param Container $container The Laravel container for resolving dependencies
     * @param array $cqrsMappings CQRS mappings from configuration
     */
    public function __construct(
        protected Container $container,
        protected array $cqrsMappings = [],
    ) {
        // This could be further extended to build a handler map from configuration
        // and to use reflection to find handler methods on aggregates
    }

    /**
     * Dispatch a command to its handler
     *
     * @param Command $command The command to dispatch
     * @return mixed The result of the command execution, if any
     * @throws InvalidArgumentException If no handler is found for the command
     */
    public function dispatch(Command $command): mixed
    {
        $commandClass = get_class($command);
        $aggregateId = $command->getAggregateId();
        $aggregateClass = $this->findAggregateClassForCommand($commandClass);
        $handlerMethod = $this->findHandlerMethodForCommand($aggregateClass, $command);

        /** @var AggregateRootRepository $repository */
        $repository = $this->container->make($aggregateClass . 'Repository');

        /** @var AggregateRoot $aggregate */
        $aggregate = $repository->retrieve($aggregateId);

        // Execute the handler method with the command
        $result = $aggregate->$handlerMethod($command);

        // Persist the aggregate changes
        $aggregate->persist();

        return $result;
    }

    /**
     * Determine if the dispatcher can handle a specific command
     *
     * @param Command|string $command The command or command class
     * @return bool
     */
    public function canDispatch(Command|string $command): bool
    {
        $commandClass = is_string($command) ? $command : get_class($command);

        try {
            $this->findAggregateClassForCommand($commandClass);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Find the aggregate class for a command using configuration or naming conventions
     *
     * @param string $commandClass
     * @return string
     * @throws InvalidArgumentException If no aggregate is found for the command
     */
    protected function findAggregateClassForCommand(string $commandClass): string
    {
        // First, try to find the mapping in configuration
        foreach ($this->cqrsMappings as $mapping) {
            if ($mapping['command'] === $commandClass && isset($mapping['aggregate'])) {
                return $mapping['aggregate'];
            }
        }

        // If not in config, try to infer from naming conventions
        // Example: App\Commands\RegisterUserCommand -> App\Aggregates\UserAggregate
        $commandClassName = (new ReflectionClass($commandClass))->getShortName();

        // Extract the resource name from the command class name
        // RegisterUserCommand -> User
        if (preg_match('/^(?:Create|Update|Delete|Register|Activate|Deactivate|Cancel|Approve|Reject|Process)(.+?)Command$/', $commandClassName, $matches)) {
            $resourceName = $matches[1]; // User

            // Try common aggregate naming patterns
            $possibleAggregateClasses = [
                "\\App\\Aggregates\\{$resourceName}Aggregate",
                "\\App\\Domain\\Aggregates\\{$resourceName}Aggregate",
                "\\App\\CQRS\\Aggregates\\{$resourceName}Aggregate",
                "\\Domain\\{$resourceName}\\{$resourceName}Aggregate",
            ];

            foreach ($possibleAggregateClasses as $aggregateClass) {
                if (class_exists($aggregateClass)) {
                    return $aggregateClass;
                }
            }
        }

        throw new InvalidArgumentException("No aggregate found for command {$commandClass}");
    }

    /**
     * Find the handler method on an aggregate for a command
     *
     * @param string $aggregateClass
     * @param Command $command
     * @return string
     */
    protected function findHandlerMethodForCommand(string $aggregateClass, Command $command): string
    {
        $reflection = new ReflectionClass($aggregateClass);
        $commandClass = get_class($command);
        $commandShortName = (new ReflectionClass($commandClass))->getShortName();

        // First, look for a method with the same name as the command (camelCased)
        // RegisterUserCommand -> registerUser
        $methodName = lcfirst(preg_replace('/Command$/', '', $commandShortName));
        if ($reflection->hasMethod($methodName)) {
            return $methodName;
        }

        // Next, look for a method that accepts the command as a parameter
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $parameters = $method->getParameters();
            if (count($parameters) === 1) {
                $parameterType = $parameters[0]->getType();
                if ($parameterType && (
                    $parameterType->getName() === $commandClass ||
                    is_subclass_of($commandClass, $parameterType->getName())
                )) {
                    return $method->getName();
                }
            }
        }

        // Finally, fall back to a generic handle method
        if ($reflection->hasMethod('handle')) {
            return 'handle';
        }

        // If no appropriate method is found, default to the command name without 'Command'
        return $methodName;
    }
}
