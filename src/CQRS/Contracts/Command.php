<?php

namespace Kwidoo\Lifecycle\CQRS\Contracts;

/**
 * Marker interface for Command DTOs
 *
 * Commands represent intentions to change the system state.
 * They should be immutable and contain all data required to perform the action.
 */
interface Command
{
    /**
     * Get the unique identifier for the aggregate this command targets
     *
     * @return string|int
     */
    public function getAggregateId(): string|int;
}
