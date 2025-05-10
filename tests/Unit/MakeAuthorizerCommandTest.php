<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Kwidoo\Lifecycle\Commands\MakeAuthorizerCommand;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;

class MakeAuthorizerCommandTest extends TestCase
{
    protected $filesystem;
    protected $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->command = new MakeAuthorizerCommand($this->filesystem);
        $this->app->singleton('command.make.authorizer', function ($app) {
            return $this->command;
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCommandMakesAuthorizerClassWithDefaultPath()
    {
        // Set up expectations
        $this->filesystem->shouldReceive('isDirectory')
            ->once()
            ->with(app_path('Authorizers'))
            ->andReturn(true);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(app_path('Authorizers/TestAuthorizer.php'))
            ->andReturn(false);

        $this->filesystem->shouldReceive('get')
            ->once()
            ->with($this->command->getStubPath())
            ->andReturn('<?php namespace {{ namespace }}; class {{ class }} {}');

        $this->filesystem->shouldReceive('put')
            ->once()
            ->with(
                app_path('Authorizers/TestAuthorizer.php'),
                '<?php namespace App\\Authorizers; class TestAuthorizer {}'
            )
            ->andReturn(true);

        // Execute command
        $this->artisan('make:authorizer', ['name' => 'Test'])
            ->expectsOutput('✓ Authorizer created successfully.')
            ->assertExitCode(0);
    }

    public function testCommandMakesAuthorizerClassWithCustomPath()
    {
        // Set up expectations
        $this->filesystem->shouldReceive('isDirectory')
            ->once()
            ->with('/custom/path')
            ->andReturn(false);

        $this->filesystem->shouldReceive('makeDirectory')
            ->once()
            ->with('/custom/path', 0755, true)
            ->andReturn(true);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with('/custom/path/CustomAuthorizer.php')
            ->andReturn(false);

        $this->filesystem->shouldReceive('get')
            ->once()
            ->with($this->command->getStubPath())
            ->andReturn('<?php namespace {{ namespace }}; class {{ class }} {}');

        $this->filesystem->shouldReceive('put')
            ->once()
            ->with(
                '/custom/path/CustomAuthorizer.php',
                '<?php namespace App\\Authorizers; class CustomAuthorizer {}'
            )
            ->andReturn(true);

        // Execute command
        $this->artisan('make:authorizer', [
            'name' => 'Custom',
            '--path' => '/custom/path'
        ])
            ->expectsOutput('✓ Authorizer created successfully.')
            ->assertExitCode(0);
    }

    public function testCommandFailsIfAuthorizerExists()
    {
        // Set up expectations
        $this->filesystem->shouldReceive('isDirectory')
            ->once()
            ->with(app_path('Authorizers'))
            ->andReturn(true);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(app_path('Authorizers/ExistingAuthorizer.php'))
            ->andReturn(true);

        // Execute command
        $this->artisan('make:authorizer', ['name' => 'Existing'])
            ->expectsOutput('Authorizer ExistingAuthorizer already exists!')
            ->assertExitCode(1);
    }

    public function testCommandAppendsAuthorizerSuffixIfMissing()
    {
        // Set up expectations
        $this->filesystem->shouldReceive('isDirectory')
            ->once()
            ->with(app_path('Authorizers'))
            ->andReturn(true);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(app_path('Authorizers/UserAuthorizer.php'))
            ->andReturn(false);

        $this->filesystem->shouldReceive('get')
            ->once()
            ->with($this->command->getStubPath())
            ->andReturn('<?php namespace {{ namespace }}; class {{ class }} {}');

        $this->filesystem->shouldReceive('put')
            ->once()
            ->with(
                app_path('Authorizers/UserAuthorizer.php'),
                '<?php namespace App\\Authorizers; class UserAuthorizer {}'
            )
            ->andReturn(true);

        // Execute command
        $this->artisan('make:authorizer', ['name' => 'User'])
            ->expectsOutput('✓ Authorizer created successfully.')
            ->assertExitCode(0);
    }
}
