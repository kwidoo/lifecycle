<?php

namespace Kwidoo\Lifecycle\CQRS\Repositories;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Kwidoo\Lifecycle\CQRS\Contracts\ReadModelRepository;

/**
 * Factory for resolving ReadModelRepository instances
 */
class ReadModelRepositoryFactory
{
    /**
     * Create a new read model repository factory
     *
     * @param Container $container
     * @param array $readModelMappings
     */
    public function __construct(
        protected Container $container,
        protected array $readModelMappings = []
    ) {}

    /**
     * Resolve a read model repository for a given resource
     *
     * @param string $resource
     * @return ReadModelRepository
     * @throws InvalidArgumentException If no repository is mapped for the resource
     */
    public function resolve(string $resource): ReadModelRepository
    {
        // Check if we have a direct mapping for this resource
        if (isset($this->readModelMappings[$resource])) {
            $repositoryClass = $this->readModelMappings[$resource];
            return $this->container->make($repositoryClass);
        }

        // Try to guess the repository class name based on conventions
        $possibleRepositoryClasses = [
            "\\App\\Repositories\\{$resource}ReadModelRepository",
            "\\App\\CQRS\\Repositories\\{$resource}ReadModelRepository",
            "\\App\\Domain\\Repositories\\{$resource}ReadModelRepository",
        ];

        foreach ($possibleRepositoryClasses as $repositoryClass) {
            if (class_exists($repositoryClass)) {
                return $this->container->make($repositoryClass);
            }
        }

        throw new InvalidArgumentException("No read model repository found for resource {$resource}");
    }
}
