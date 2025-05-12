<?php

namespace Kwidoo\Lifecycle\CQRS\Commands;

use Kwidoo\Lifecycle\CQRS\Contracts\Command;

/**
 * Abstract base class for command DTOs
 *
 * Commands represent intentions to change the system state.
 * They should be immutable and contain all data required to perform the action.
 */
abstract class AbstractCommand implements Command
{
    /**
     * Create a new command instance
     *
     * @param string|int $aggregateId The ID of the aggregate this command targets
     */
    public function __construct(
        protected string|int $aggregateId
    ) {}

    /**
     * Get the unique identifier for the aggregate this command targets
     *
     * @return string|int
     */
    public function getAggregateId(): string|int
    {
        return $this->aggregateId;
    }
}
