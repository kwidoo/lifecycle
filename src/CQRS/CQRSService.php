<?php

namespace Kwidoo\Lifecycle\CQRS;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Kwidoo\Lifecycle\CQRS\Commands\CommandFactory;
use Kwidoo\Lifecycle\CQRS\Contracts\Command;
use Kwidoo\Lifecycle\CQRS\Contracts\CommandDispatcher;
use Kwidoo\Lifecycle\CQRS\Repositories\ReadModelRepositoryFactory;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

/**
 * Main service for coordinating CQRS operations
 *
 * This service provides methods for creating and dispatching commands,
 * as well as querying read models, integrating directly with the Lifecycle system.
 */
class CQRSService
{
    /**
     * Create a new CQRS service
     *
     * @param Container $container
     * @param CommandDispatcher $commandDispatcher
     * @param CommandFactory $commandFactory
     * @param ReadModelRepositoryFactory $repositoryFactory
     * @param array $config CQRS configuration
     */
    public function __construct(
        protected Container $container,
        protected CommandDispatcher $commandDispatcher,
        protected CommandFactory $commandFactory,
        protected ReadModelRepositoryFactory $repositoryFactory,
        protected array $config = []
    ) {}

    /**
     * Handle a CQRS command using data from the lifecycle context
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return mixed
     * @throws InvalidArgumentException If command creation fails
     */
    public function handleCommand(LifecycleContextData|LifecycleData $data): mixed
    {
        // Create a command from the lifecycle data
        $command = $this->commandFactory->createFromLifecycleData($data);

        // Dispatch the command
        return $this->commandDispatcher->dispatch($command);
    }

    /**
     * Directly dispatch a command
     *
     * @param Command $command
     * @return mixed
     */
    public function dispatchCommand(Command $command): mixed
    {
        return $this->commandDispatcher->dispatch($command);
    }

    /**
     * Query read models using data from the lifecycle context
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return mixed
     */
    public function query(LifecycleContextData|LifecycleData $data): mixed
    {
        $resource = $data->resource;
        $action = $data->action;
        $context = $data->context;

        // Resolve the appropriate read model repository
        $repository = $this->repositoryFactory->resolve($resource);

        // Based on the action, execute the appropriate query method
        switch ($action) {
            case 'get':
            case 'find':
            case 'retrieve':
            case 'show':
                if (isset($context['id'])) {
                    return $repository->findById($context['id']);
                }
                break;

            case 'list':
            case 'index':
            case 'search':
            case 'query':
                $criteria = $context['criteria'] ?? $context['filters'] ?? $context;
                $orderBy = $context['orderBy'] ?? $context['sort'] ?? [];
                $limit = $context['limit'] ?? $context['perPage'] ?? null;
                $offset = $context['offset'] ?? ($context['page'] ?? 0) * ($limit ?? 0);

                return $repository->findByCriteria($criteria, $orderBy, $limit, $offset);

            default:
                // For custom actions, try to find a matching method on the repository
                $methodName = $action;
                if (method_exists($repository, $methodName)) {
                    return $repository->$methodName($context);
                }

                // If no specific method found, fall back to findByCriteria
                return $repository->findByCriteria($context);
        }

        throw new InvalidArgumentException(
            "Could not determine how to query {$resource} with action {$action}"
        );
    }
}
