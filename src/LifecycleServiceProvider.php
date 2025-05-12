<?php

namespace Kwidoo\Lifecycle;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\ServiceProvider;
use Kwidoo\Lifecycle\Commands\LifecycleSetupCommand;
use Kwidoo\Lifecycle\Commands\MakeAuthorizerCommand;
use Kwidoo\Lifecycle\Commands\MakeFlowCommand;
use Kwidoo\Lifecycle\Contracts\Factories\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Factories\LoggableFactory;
use Kwidoo\Lifecycle\Contracts\Features\Authorizer;
use Kwidoo\Lifecycle\Contracts\Features\Cacheable;
use Kwidoo\Lifecycle\Contracts\Features\RateLimitable;
use Kwidoo\Lifecycle\Contracts\Features\Retryable;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Eventable;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Loggable;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Transactional;
use Kwidoo\Lifecycle\Contracts\Resolvers\AuthorizerResolver;
use Kwidoo\Lifecycle\Contracts\Resolvers\StrategyResolver;
use Kwidoo\Lifecycle\CQRS\Commands\CommandFactory;
use Kwidoo\Lifecycle\CQRS\Commands\DefaultCommandDispatcher;
use Kwidoo\Lifecycle\CQRS\Contracts\CommandDispatcher;
use Kwidoo\Lifecycle\CQRS\CQRSService;
use Kwidoo\Lifecycle\CQRS\Repositories\ReadModelRepositoryFactory;
use Kwidoo\Lifecycle\Factories\DefaultAuthorizerFactory;
use Kwidoo\Lifecycle\Factories\DefaultLoggableFactory;
use Kwidoo\Lifecycle\Factories\LifecycleMiddlewareFactory;
use Kwidoo\Lifecycle\Features\Authorizers\DefaultAuthorizer;
use Kwidoo\Lifecycle\Features\Cache\DefaultCacheable;
use Kwidoo\Lifecycle\Features\Event\DefaultEventable;
use Kwidoo\Lifecycle\Features\Retry\DefaultRetryable;
use Kwidoo\Lifecycle\Features\Log\DefaultLoggable;
use Kwidoo\Lifecycle\Features\RateLimit\DefaultRateLimitable;
use Kwidoo\Lifecycle\Features\Transaction\DefaultTransactional;
use Kwidoo\Lifecycle\Resolvers\AuthAwareResolver;
use Kwidoo\Lifecycle\Support\Helpers\LifecycleInstallerService;

class LifecycleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lifecycle');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'lifecycle');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('lifecycle.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/lifecycle'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/lifecycle'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/lifecycle'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                MakeAuthorizerCommand::class,
                LifecycleSetupCommand::class,
                MakeFlowCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'lifecycle');

        // Register Installer Services
        $this->app->singleton(LifecycleInstallerService::class, function ($app) {
            return new LifecycleInstallerService($app->make('files'));
        });

        // Register Authorizer components
        $this->app->bind(Authorizer::class, DefaultAuthorizer::class);
        $this->app->bind(AuthorizerFactory::class, DefaultAuthorizerFactory::class);
        $this->app->bind(AuthorizerResolver::class, AuthAwareResolver::class);

        // Register Pipeline
        $this->app->bind(Pipeline::class, function ($app) {
            return new Pipeline($app);
        });

        // Register Resolver components - new interfaces


        // Register LifecycleMiddlewareFactory with the new resolver
        $this->app->bind(LifecycleMiddlewareFactory::class, function ($app) {
            // Use the new StrategyResolver instead of the legacy resolver
            return new LifecycleMiddlewareFactory(
                $app->make(StrategyResolver::class)
            );
        });
        $this->app->bind(LoggableFactory::class, DefaultLoggableFactory::class);
        $this->app->bind(Retryable::class, DefaultRetryable::class);
        $this->app->bind(Eventable::class, DefaultEventable::class);
        $this->app->bind(Loggable::class, DefaultLoggable::class);
        $this->app->bind(Transactional::class, DefaultTransactional::class);
        $this->app->bind(Cacheable::class, DefaultCacheable::class);
        $this->app->bind(RateLimitable::class, DefaultRateLimitable::class);


        // Register CQRS components
        $this->registerCQRSComponents();
    }

    /**
     * Register CQRS components with the container
     */
    protected function registerCQRSComponents()
    {
        // Command dispatcher
        $this->app->bind(CommandDispatcher::class, function ($app) {
            return new DefaultCommandDispatcher(
                container: $app,
                cqrsMappings: config('lifecycle.cqrs_mappings', []),
            );
        });

        // Command factory
        $this->app->bind(CommandFactory::class, function ($app) {
            return new CommandFactory(
                container: $app,
                cqrsMappings: config('lifecycle.cqrs_mappings', []),
            );
        });

        // Read model repository factory
        $this->app->bind(ReadModelRepositoryFactory::class, function ($app) {
            return new ReadModelRepositoryFactory(
                container: $app,
                readModelMappings: config('lifecycle.read_models', []),
            );
        });

        // Main CQRS Service
        $this->app->bind(CQRSService::class, function ($app) {
            return new CQRSService(
                container: $app,
                commandDispatcher: $app->make(CommandDispatcher::class),
                commandFactory: $app->make(CommandFactory::class),
                repositoryFactory: $app->make(ReadModelRepositoryFactory::class),
                config: [
                    'cqrs_transactions' => config('lifecycle.cqrs_transactions', []),
                    'cqrs_events' => config('lifecycle.cqrs_events', []),
                ],
            );
        });
    }
}
