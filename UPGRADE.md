# 🚀 Upgrade Guide: `kwidoo/lifecycle` v1.1.0 → v2.0.0

> **Version**: 2.0.0
> **Release date**: \[INSERT RELEASE DATE]
> **Minimum Laravel version**: 10.x
> **PSR Compatibility**: PSR-12
> **Required PHP Version**: 8.1+

---

## 🔥 BREAKING CHANGES SUMMARY

| Area                  | Change Description                                                               |
| --------------------- | -------------------------------------------------------------------------------- |
| Strategies & Features | Strategy/Feature split is removed or unified; new directory layout adopted       |
| Middleware            | All `WithXxxMiddleware` renamed and moved; interfaces now injected consistently  |
| DTOs                  | `LifecycleData` split into `LifecycleContextData` and `LifecycleResultData`      |
| Resolvers             | Refactored factory and resolver classes; config-driven strategy loading enforced |
| Directory Structure   | Entire layout restructured under `Strategies/{Domain}/`, `Middleware/`, etc.     |
| Legacy Code           | Removed legacy `Lifecycle/`, `WithXxx`, and incomplete interfaces (`Traceable`)  |
| Configuration         | `lifecycle.php` config must be updated to match new keys and structure           |

---

## 🧭 MIGRATION STEPS

### 1. ✅ Update Composer Dependency

```bash
composer require kwidoo/lifecycle:^2.0
```

---

### 2. 🧹 Remove Usage of Deprecated `WithXxxMiddleware` Classes

**Old (v1.1.0):**

```php
$pipeline->push(new WithLoggingMiddleware($logger));
```

**New (v2.0.0):**

```php
$pipeline->push(new LoggingMiddleware($logStrategy));
```

See new middleware under `Kwidoo\Lifecycle\Core\Middleware\`.

---

### 3. 📦 Update Strategy & Middleware Bindings in ServiceProvider

**Old binding (v1.1.0):**

```php
LogStrategy::class => DefaultLogStrategy::class
```

**New (v2.0.0):**

```php
Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy::class =>
    Kwidoo\Lifecycle\Strategies\Logging\DefaultLogStrategy::class,
```

> 🧠 Tip: Use `ConfigDrivenStrategyResolver` for runtime context switching.

---

### 4. 🧱 Migrate from `LifecycleData` to `LifecycleContextData` and `LifecycleResultData`

**Old:**

```php
$data = new LifecycleData(action: 'create', resource: 'users', context: $payload);
```

**New:**

```php
$context = new LifecycleContextData(action: 'create', resource: 'users', context: $payload);
$result = new LifecycleResultData(result: $response);
```

> ✅ The old `LifecycleData` still exists for backward compatibility, but its use is discouraged.

---

### 5. 🧰 Refactor Factory Usage

**Old:**

```php
$factory = app(DefaultLifecycleFactory::class);
```

**New:**

```php
$factory = app(Kwidoo\Lifecycle\Factories\LifecyclePipelineFactoryInterface::class);
```

> 🔁 All factories now follow interface-based injection and are context-aware.

---

### 6. 🔐 Clean Up Legacy Usage

Remove these from your codebase:

* `WithEvents`, `WithLogging`, `WithRetry`, etc.
* `Lifecycle/LegacyLifecycle.php`, etc.
* Interfaces like `Traceable`, `Command`, `Query` if not reactivated.

---

### 7. ⚙️ Update Config (`config/lifecycle.php`)

If you're using config-driven setup:

**Old:**

```php
'log_strategy' => DefaultLogStrategy::class,
```

**New:**

```php
'strategies' => [
    'logging' => Kwidoo\Lifecycle\Strategies\Logging\DefaultLogStrategy::class,
    'events' => Kwidoo\Lifecycle\Strategies\Events\DefaultEventStrategy::class,
    'retry' => Kwidoo\Lifecycle\Strategies\Retry\DefaultRetryStrategy::class,
    'transactions' => Kwidoo\Lifecycle\Strategies\Transactions\DefaultTransactionStrategy::class,
    // etc.
],
```

---

### 8. 🧪 Run Updated Tests

* Update test mocks to reference new strategy/middleware structure
* Migrate tests that expect `LifecycleData` to test `LifecycleContextData` and `LifecycleResultData` separately
* Focus on:

  * Middleware pipelines
  * Strategy feature toggles
  * Edge cases (retry, error, auth off/on)

---

### 9. 📖 Review Docs & Examples

Review updated examples in:

* `docs/usage.md`
* `docs/testing.md`
* `docs/configuration.md`

Ensure all lifecycle calls now use the new class names and updated DTOs.

---

## 📌 What Was Removed?

* `WithXxx`-prefixed middleware and strategies
* Legacy `Lifecycle/` directory
* Unused interfaces: `Command`, `Query`, `Traceable`
* Deprecated factory methods not compatible with config-driven resolution

---

## 🧪 Sample Updated Usage

```php
$context = new LifecycleContextData(
    action: 'store',
    resource: 'profile',
    context: $payload
);

$options = LifecycleOptionsData::make()
    ->withoutLogging()
    ->withoutAuth();

return $this->lifecycle->run($context, function ($ctx) {
    return $this->profileService->store($ctx->context);
}, $options);
```

---

## 📋 To-Do Before Production

* [ ] Update your container bindings to new class names
* [ ] Migrate all middleware references to renamed versions
* [ ] Replace all usages of `LifecycleData` with new DTOs
* [ ] Validate config changes
* [ ] Re-run test suite
