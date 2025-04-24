<?php

namespace Kwidoo\Lifecycle;

use Illuminate\Support\ServiceProvider;
use Kwidoo\Lifecycle\Authorizers\DefaultAuthorizer;
use Kwidoo\Lifecycle\Commands\MakeAuthorizerCommand;
use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Eventable;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Loggable;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Transactional;
use Kwidoo\Lifecycle\Factories\DefaultAuthorizerFactory;
use Kwidoo\Lifecycle\Lifecycle\DefaultEventable;
use Kwidoo\Lifecycle\Lifecycle\DefaultLifecycle;
use Kwidoo\Lifecycle\Lifecycle\DefaultLifecycleStrategyResolver;
use Kwidoo\Lifecycle\Lifecycle\DefaultLoggable;
use Kwidoo\Lifecycle\Lifecycle\DefaultTransactional;
use Kwidoo\Lifecycle\Strategies\WithEvents;
use Kwidoo\Lifecycle\Strategies\WithLogging;
use Kwidoo\Lifecycle\Strategies\WithoutEvents;
use Kwidoo\Lifecycle\Strategies\WithoutLogging;
use Kwidoo\Lifecycle\Strategies\WithoutTransactions;
use Kwidoo\Lifecycle\Strategies\WithTransactions;

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

        // Register Authorizer components
        $this->app->bind(Authorizer::class, DefaultAuthorizer::class);
        $this->app->bind(AuthorizerFactory::class, DefaultAuthorizerFactory::class);

        // Register Lifecycle components
        $this->app->bind(Lifecycle::class, DefaultLifecycle::class);
        $this->app->bind(Eventable::class, DefaultEventable::class);
        $this->app->bind(Loggable::class, DefaultLoggable::class);
        $this->app->bind(Transactional::class, DefaultTransactional::class);

        $this->app->bind(LifecycleStrategyResolver::class, function ($app) {
            return new DefaultLifecycleStrategyResolver(
                eventableStrategies: [
                    true => $app->make(WithEvents::class),
                    false => $app->make(WithoutEvents::class),
                ],
                loggingStrategies: [
                    true => $app->make(WithLogging::class),
                    false => $app->make(WithoutLogging::class),
                ],
                transactionStrategies: [
                    true => $app->make(WithTransactions::class),
                    false => $app->make(WithoutTransactions::class),
                ],
            );
        });
    }
}
