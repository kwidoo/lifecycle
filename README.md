# Lifecycle

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kwidoo/lifecycle.svg?style=flat-square)](https://packagist.org/packages/kwidoo/lifecycle)
[![Tests](https://github.com/kwidoo/lifecycle/actions/workflows/tests.yml/badge.svg)](https://github.com/kwidoo/lifecycle/actions/workflows/tests.yml)
[![Code Style](https://github.com/kwidoo/lifecycle/actions/workflows/code-style.yml/badge.svg)](https://github.com/kwidoo/lifecycle/actions/workflows/code-style.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/kwidoo/lifecycle.svg?style=flat-square)](https://packagist.org/packages/kwidoo/lifecycle)

A Laravel package that provides a flexible lifecycle management system for your application operations. This package helps you implement consistent patterns for handling events, transactions, logging, authorization, caching, and rate limiting across your Laravel application.

## Features

- 🛡️ **Authorization Management**: Control access with configurable authorization strategies
- 🔄 **Transaction Management**: Wrap operations in database transactions
- 📊 **Logging**: Automatically log actions, results, and errors
- 📡 **Event Dispatching**: Dispatch events before and after operations
- 💾 **Caching**: Cache operation results for improved performance
- 🚦 **Rate Limiting**: Protect against abuse with configurable rate limiting
- 🔁 **Retry Handling**: Automatically retry failed operations with backoff
- 🧩 **Strategy Pattern**: Choose which features to enable for each operation
- 🔌 **Extensible**: Create custom strategies for your specific requirements

## Installation

You can install the package via composer:

```bash
composer require kwidoo/lifecycle
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Kwidoo\\Lifecycle\\LifecycleServiceProvider"
```

This will create a `config/lifecycle.php` file where you can configure default behaviors.

## Usage

### Basic Usage

```php
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Contracts\Lifecycle;

class YourService
{
    public function __construct(protected Lifecycle $lifecycle) {}

    public function performAction($context)
    {
        $contextData = new LifecycleContextData(
            action: 'create',
            resource: 'users',
            context: $context
        );

        return $this->lifecycle->run($contextData, function ($contextData) {
            // Your business logic here
            $result = $this->createUser($contextData->context);

            return $result;
        }, new LifecycleOptionsData());
    }
}
```

### Customizing Strategy Options

You can use the fluent interface to customize which strategies are enabled:

```php
// Configure strategies with fluent interface
$result = $this->lifecycle->run(
    $contextData,
    function ($contextData) {
        // Business logic
        return $result;
    },
    (new LifecycleOptionsData())
        ->withoutTrx()       // Disable transactions
        ->withEvents()       // Enable events
        ->withoutLogging()   // Disable logging
        ->withCache()        // Enable caching
        ->withRetry()        // Enable retry on failure
);
```

### Lifecycle Data

The package now uses separate data objects for input and output:

```php
// Input context data - immutable
$contextData = new LifecycleContextData(
    action: 'update',      // The action being performed (create, update, delete, etc.)
    resource: 'products',  // The resource being operated on
    context: $product,     // The contextual data for the operation
);

// Execute with lifecycle
$resultData = $this->lifecycle->run($contextData, function($contextData) {
    // Business logic - process the context
    $result = processData($contextData->context);

    // Return the result
    return $result;
});
```

### Events

Events are dispatched with the following naming pattern:

- Before operation: `before.{resource}.{action}`
- After operation: `after.{resource}.{action}`
- On error: `error.{resource}.{action}`

## Advanced Usage

### Custom Authorizers

You can create custom authorizers for specific resources:

```php
namespace App\Authorizers;

use Kwidoo\Lifecycle\Authorizers\DefaultAuthorizer;

class ProductAuthorizer extends DefaultAuthorizer
{
    public function authorize(string $action, $context): bool
    {
        // Custom authorization logic for products
        if ($action === 'delete' && !$this->userCanDeleteProduct($context)) {
            return false;
        }

        return true;
    }

    protected function userCanDeleteProduct($product): bool
    {
        return auth()->user()->hasPermission('delete-products');
    }
}
```

### Custom Strategy Implementations

You can create custom strategies by implementing the respective interfaces:

```php
namespace App\Strategies\Logging;

use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

class EnhancedLogStrategy implements LogStrategy
{
    public function execute(LifecycleContextData $data, callable $callback): mixed
    {
        // Custom pre-execution logging
        $this->logWithMetadata($data, 'before');

        // Execute the callback
        $result = $callback();

        // Custom post-execution logging
        $this->logWithMetadata($data, 'after', $result);

        return $result;
    }

    public function logPhase(LifecycleContextData $data, string $phase, string $level = 'info'): void
    {
        // Your custom logging implementation
    }

    protected function logWithMetadata(LifecycleContextData $data, string $phase, $result = null): void
    {
        // Enhanced logging with additional metadata
    }
}
```

### Using Caching

Enable caching for frequently-used operations:

```php
$result = $this->lifecycle->run(
    $contextData,
    function ($contextData) {
        // Expensive operation that will be cached
        return $this->repository->fetchExpensiveData($contextData->context);
    },
    (new LifecycleOptionsData())
        ->withCache()  // Results will be cached
);
```

### Rate Limiting

Protect your application from abuse:

```php
$result = $this->lifecycle->run(
    $contextData,
    function ($contextData) {
        // Operation that needs rate limiting
        return $this->processSensitiveOperation($contextData->context);
    },
    (new LifecycleOptionsData())
        ->withRateLimit()  // Apply rate limiting
);
```

## Testing

```bash
composer test
```

The package includes comprehensive test coverage for all components including:

- Unit tests for all service components
- Strategy implementation tests
- Feature/integration tests demonstrating real-world use cases

Tests are automatically run via GitHub Actions when code is pushed to the repository.

## Upgrading

If you're upgrading from a previous version, please see the [Upgrade Guide](docs/upgrade-guide.md) for detailed instructions.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security issues, please email <oleg@pashkovsky.me> instead of using the issue tracker.

## Credits

- [Oleg Pashkovsky](https://github.com/kwidoo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
