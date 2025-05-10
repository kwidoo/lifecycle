<?php

namespace Kwidoo\Lifecycle\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class LifecycleContextData extends Data
{
    /**
     * @param string $action
     * @param string $resource
     * @param mixed $context
     * @param float|null $startedAt
     */
    public function __construct(
        #[Required()]
        public readonly string $action,
        #[Required()]
        public readonly string $resource,
        public readonly mixed $context = null,
        public readonly ?float $startedAt = null,
    ) {}

    /**
     * Create a new instance with updated properties
     *
     * @param array $parameters
     * @return self
     */
    public function copy(...$parameters): self
    {
        return new self(
            action: $parameters['action'] ?? $this->action,
            resource: $parameters['resource'] ?? $this->resource,
            context: $parameters['context'] ?? $this->context,
            startedAt: $parameters['startedAt'] ?? $this->startedAt,
        );
    }

    /**
     * Create a new instance with the current timestamp as the startedAt value
     *
     * @return self
     */
    public function withStartTime(): self
    {
        return $this->copy(startedAt: microtime(true));
    }

    /**
     * Convert to array for debug or logging purposes
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'resource' => $this->resource,
            'context' => $this->context,
            'startedAt' => $this->startedAt,
        ];
    }
}
