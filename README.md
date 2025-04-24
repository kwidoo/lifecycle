# Lifecycle

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kwidoo/lifecycle.svg?style=flat-square)](https://packagist.org/packages/kwidoo/lifecycle)
[![Build Status](https://img.shields.io/travis/kwidoo/lifecycle/master.svg?style=flat-square)](https://travis-ci.org/kwidoo/lifecycle)
[![Quality Score](https://img.shields.io/scrutinizer/g/kwidoo/lifecycle.svg?style=flat-square)](https://scrutinizer-ci.com/g/kwidoo/lifecycle)
[![Total Downloads](https://img.shields.io/packagist/dt/kwidoo/lifecycle.svg?style=flat-square)](https://packagist.org/packages/kwidoo/lifecycle)

A Laravel package that provides a flexible lifecycle management system for your application operations. This package helps you implement consistent patterns for handling events, transactions, logging, and authorization across your Laravel application.

## Features

- 🛡️ **Authorization Management**: Control access with configurable authorization strategies
- 🔄 **Transaction Management**: Wrap operations in database transactions
- 📊 **Logging**: Automatically log actions, results, and errors
- 📡 **Event Dispatching**: Dispatch events before and after operations
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
use  Kwidoo\Lifecycle\Data\LifecycleData;

use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\Lifecycle;

class YourService
{
    public function __construct(protected Lifecycle $lifecycle) {}

    public function performAction($context)
    {
        $data = new LifecycleData(
            action: 'create',
            resource: 'users',
            context: $context
        );

        return $this->lifecycle->run($data, function ($data) {
            // Your business logic here
            return $result;
        }, new LifecycleOptionsData());
    }
}
```

### Customizing Options

You can customize which features are enabled:

```php
// Disable transactions
$options = new LifecycleOptionsData();
$options = $options->withoutTrx();

// Disable events
$options = $options->withoutEvents();

// Disable logging
$options = $options->withoutLogging();

// Disable authorization
$options = $options->withoutAuth();

// Disable all features
$options = $options->withoutAll();
```

### Lifecycle Data

The `LifecycleData` class encapsulates the information about an operation:

```php
$data = new LifecycleData(
    action: 'update',      // The action being performed (create, update, delete, etc.)
    resource: 'products',  // The resource being operated on
    context: $product,     // The contextual data for the operation
);
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
        // Custom authorization logic
        return true;
    }
}
```

### Custom Strategy Implementations

You can create custom strategies for events, logging, or transactions by implementing the respective interfaces:

- `EventableStrategy`
- `LoggingStrategy`
- `TransactionStrategy`

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security issues, please email oleg@pashkovsky.me instead of using the issue tracker.

## Credits

- [Oleg Pashkovsky](https://github.com/kwidoo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
