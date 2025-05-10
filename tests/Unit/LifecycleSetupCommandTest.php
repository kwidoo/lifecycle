<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Commands\LifecycleSetupCommand;
use Kwidoo\Lifecycle\Support\Helpers\LifecycleInstallerService;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;

class LifecycleSetupCommandTest extends TestCase
{
    protected $installerService;
    protected $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installerService = Mockery::mock(LifecycleInstallerService::class);
        $this->command = new LifecycleSetupCommand($this->installerService);
        $this->app->singleton('command.lifecycle.setup', function ($app) {
            return $this->command;
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCommandRunsInstallerService()
    {
        // Sample installation results for testing
        $installResults = [
            'config' => [
                'status' => 'success',
                'message' => 'File published successfully.',
                'path' => 'config/lifecycle.php'
            ],
            'authorizer' => [
                'status' => 'success',
                'message' => 'File created successfully.',
                'path' => 'app/Authorizers/DefaultAuthorizer.php'
            ],
            'middleware' => [
                'status' => 'success',
                'message' => 'File created successfully.',
                'path' => 'app/Http/Middleware/LifecycleMiddleware.php'
            ],
            'provider' => [
                'status' => 'success',
                'message' => 'File created successfully.',
                'path' => 'app/Providers/LifecycleServiceProvider.php'
            ],
            'provider_registration' => [
                'status' => 'success',
                'message' => 'Service provider registered in app config.',
                'path' => 'config/app.php'
            ]
        ];

        // Set up expectations
        $this->installerService->shouldReceive('install')
            ->once()
            ->with(false, false) // force=false, dryRun=false
            ->andReturn($installResults);

        // Execute command
        $this->artisan('lifecycle:setup')
            ->expectsOutput('Setting up Lifecycle package...')
            ->expectsOutput('Lifecycle setup completed successfully.')
            ->assertExitCode(0);
    }

    public function testCommandRunsInDryRunMode()
    {
        // Sample installation results for testing
        $installResults = [
            'config' => [
                'status' => 'simulated',
                'message' => 'Would publish file.',
                'path' => 'config/lifecycle.php'
            ],
            'authorizer' => [
                'status' => 'simulated',
                'message' => 'Would write file.',
                'path' => 'app/Authorizers/DefaultAuthorizer.php'
            ],
            'middleware' => [
                'status' => 'simulated',
                'message' => 'Would write file.',
                'path' => 'app/Http/Middleware/LifecycleMiddleware.php'
            ],
            'provider' => [
                'status' => 'simulated',
                'message' => 'Would write file.',
                'path' => 'app/Providers/LifecycleServiceProvider.php'
            ],
            'provider_registration' => [
                'status' => 'simulated',
                'message' => 'Would register service provider in app config.',
                'path' => 'config/app.php'
            ]
        ];

        // Set up expectations
        $this->installerService->shouldReceive('install')
            ->once()
            ->with(false, true) // force=false, dryRun=true
            ->andReturn($installResults);

        // Execute command
        $this->artisan('lifecycle:setup', ['--dry-run' => true])
            ->expectsOutput('Running in dry-run mode. No files will be changed.')
            ->expectsOutput('Setting up Lifecycle package...')
            ->expectsOutput('Dry run complete. Run without --dry-run to apply changes.')
            ->assertExitCode(0);
    }

    public function testCommandWithForceOption()
    {
        // Sample installation results for testing
        $installResults = [
            'config' => [
                'status' => 'success',
                'message' => 'File published successfully.',
                'path' => 'config/lifecycle.php'
            ],
            'authorizer' => [
                'status' => 'success',
                'message' => 'File created successfully.',
                'path' => 'app/Authorizers/DefaultAuthorizer.php'
            ],
            'middleware' => [
                'status' => 'success',
                'message' => 'File created successfully.',
                'path' => 'app/Http/Middleware/LifecycleMiddleware.php'
            ],
            'provider' => [
                'status' => 'success',
                'message' => 'File created successfully.',
                'path' => 'app/Providers/LifecycleServiceProvider.php'
            ],
            'provider_registration' => [
                'status' => 'success',
                'message' => 'Service provider registered in app config.',
                'path' => 'config/app.php'
            ]
        ];

        // Set up expectations
        $this->installerService->shouldReceive('install')
            ->once()
            ->with(true, false) // force=true, dryRun=false
            ->andReturn($installResults);

        // Execute command with force confirmation
        $this->artisan('lifecycle:setup', ['--force' => true])
            ->expectsConfirmation('The --force option will overwrite existing files. Do you want to continue?', 'yes')
            ->expectsOutput('Setting up Lifecycle package...')
            ->expectsOutput('Lifecycle setup completed successfully.')
            ->assertExitCode(0);
    }
}
