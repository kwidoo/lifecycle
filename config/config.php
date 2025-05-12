<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | CQRS Mappings
    |--------------------------------------------------------------------------
    |
    | This section defines mappings between action.resource pairs and their
    | corresponding CQRS components (commands, aggregates, etc).
    |
    | Each mapping key is an action.resource string (e.g., 'user.register')
    | and its value is an array with the following possible keys:
    |
    | - command: The fully qualified class name of the Command DTO
    | - aggregate: The fully qualified class name of the AggregateRoot
    | - uuid_resolver: A callable that resolves the aggregate UUID from the context
    | - command_factory: A callable that creates the command from the context
    |
    */
    'cqrs_mappings' => [
        // Example mappings:
        'user.register' => [
            'command' => \App\Commands\RegisterUserCommand::class,
            'aggregate' => \App\Aggregates\UserAggregate::class,
            'uuid_resolver' => fn($context) => $context['id'] ?? \Illuminate\Support\Str::uuid(),
        ],
        'user.update' => [
            'command' => \App\Commands\UpdateUserCommand::class,
            'aggregate' => \App\Aggregates\UserAggregate::class,
            'uuid_resolver' => fn($context) => $context['id'],
        ],
        'order.create' => [
            'command' => \App\Commands\CreateOrderCommand::class,
            'aggregate' => \App\Aggregates\OrderAggregate::class,
            'uuid_resolver' => fn($context) => \Illuminate\Support\Str::uuid(),
            'command_factory' => [\App\Factories\OrderCommandFactory::class, 'createOrderCommand'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CQRS Read Models
    |--------------------------------------------------------------------------
    |
    | This section defines mappings for read model repositories to use in
    | CQRS query mode. Each key is a resource name, and each value is
    | the repository class responsible for querying that resource.
    |
    */
    'read_models' => [
        'user' => \App\Repositories\UserReadModelRepository::class,
        'order' => \App\Repositories\OrderReadModelRepository::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Management for CQRS
    |--------------------------------------------------------------------------
    |
    | Configure how transactions are handled in CQRS mode
    |
    */
    'cqrs_transactions' => [
        // When true, the TransactionMiddleware will wrap the aggregate persist() call
        // When false, transaction management is left to the aggregate
        'wrap_aggregate_persist' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Handling for CQRS
    |--------------------------------------------------------------------------
    |
    | Configure how Lifecycle handles its own events when in CQRS mode
    |
    */
    'cqrs_events' => [
        // When true, Lifecycle will still dispatch its own orchestration events
        // in addition to the domain events from aggregates
        'dispatch_lifecycle_events' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Configuration Options
    |--------------------------------------------------------------------------
    */
    // Add any existing config options here
];
