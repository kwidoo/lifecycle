<?php

namespace Kwidoo\Lifecycle\CQRS\Contracts;

/**
 * Interface for command dispatchers
 *
 * Command dispatchers are responsible for routing commands to their appropriate handlers,
 * typically aggregates in an event sourcing system.
 */
interface CommandDispatcher
{
    /**
     * Dispatch a command to its handler
     *
     * @param Command $command The command to dispatch
     * @return mixed The result of the command execution, if any
     */
    public function dispatch(Command $command): mixed;

    /**
     * Determine if the dispatcher can handle a specific command
     *
     * @param Command|string $command The command or command class
     * @return bool
     */
    public function canDispatch(Command|string $command): bool;
}
