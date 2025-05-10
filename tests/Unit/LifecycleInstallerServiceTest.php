<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Kwidoo\Lifecycle\Support\Helpers\LifecycleInstallerService;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;

class LifecycleInstallerServiceTest extends TestCase
{
    protected $filesystem;
    protected $installer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->installer = new LifecycleInstallerService($this->filesystem);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testPublishConfigCreatesFileWhenNotExists()
    {
        // Set up mock expectations
        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(config_path('lifecycle.php'))
            ->andReturn(false);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(Mockery::containsString('/config/config.php'))
            ->andReturn(true);

        $this->filesystem->shouldReceive('isDirectory')
            ->once()
            ->with(config_path())
            ->andReturn(true);

        $this->filesystem->shouldReceive('copy')
            ->once()
            ->withArgs(function ($source, $target) {
                return strpos($source, 'config/config.php') !== false && $target === config_path('lifecycle.php');
            })
            ->andReturn(true);

        // Call the method
        $result = $this->installer->publishConfig();

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('File published successfully.', $result['message']);
        $this->assertEquals(config_path('lifecycle.php'), $result['target']);
    }

    public function testPublishConfigSkipsWhenFileExistsAndNoForce()
    {
        // Set up mock expectations
        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(config_path('lifecycle.php'))
            ->andReturn(true);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(Mockery::containsString('/config/config.php'))
            ->andReturn(true);

        // Call the method
        $result = $this->installer->publishConfig(false);

        // Assert the result
        $this->assertEquals('skipped', $result['status']);
        $this->assertEquals('Target file already exists.', $result['message']);
    }

    public function testPublishConfigOverwritesWhenForceEnabled()
    {
        // Set up mock expectations
        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(config_path('lifecycle.php'))
            ->andReturn(true);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(Mockery::containsString('/config/config.php'))
            ->andReturn(true);

        $this->filesystem->shouldReceive('isDirectory')
            ->once()
            ->with(config_path())
            ->andReturn(true);

        $this->filesystem->shouldReceive('copy')
            ->once()
            ->withArgs(function ($source, $target) {
                return strpos($source, 'config/config.php') !== false && $target === config_path('lifecycle.php');
            })
            ->andReturn(true);

        // Call the method
        $result = $this->installer->publishConfig(true);

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('File published successfully.', $result['message']);
    }

    public function testPublishConfigSimulatesInDryRunMode()
    {
        // Set up mock expectations
        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(config_path('lifecycle.php'))
            ->andReturn(false);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with(Mockery::containsString('/config/config.php'))
            ->andReturn(true);

        // Call the method
        $result = $this->installer->publishConfig(false, true);

        // Assert the result
        $this->assertEquals('simulated', $result['status']);
        $this->assertEquals('Would publish file.', $result['message']);
    }

    public function testCreateBaseAuthorizerCreatesFile()
    {
        // Set up mock expectations
        $filePath = app_path('Authorizers/DefaultAuthorizer.php');

        $this->filesystem->shouldReceive('isDirectory')
            ->once()
            ->with(app_path('Authorizers'))
            ->andReturn(false);

        $this->filesystem->shouldReceive('makeDirectory')
            ->once()
            ->with(app_path('Authorizers'), 0755, true)
            ->andReturn(true);

        $this->filesystem->shouldReceive('exists')
            ->once()
            ->with($filePath)
            ->andReturn(false);

        $this->filesystem->shouldReceive('get')
            ->once()
            ->with(Mockery::containsString('/Commands/stubs/authorizer.stub'))
            ->andReturn('namespace {{ namespace }}; class {{ class }} {}');

        $this->filesystem->shouldReceive('put')
            ->once()
            ->withArgs(function ($path, $content) {
                return $path === app_path('Authorizers/DefaultAuthorizer.php') &&
                    strpos($content, 'namespace App\\Authorizers') !== false;
            })
            ->andReturn(true);

        // Call the method
        $result = $this->installer->createBaseAuthorizer();

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('File created successfully.', $result['message']);
    }

    public function testInstallRunsAllSteps()
    {
        // We'll just verify that install calls all the methods
        // This is a partial mock to spy on method calls
        $installer = Mockery::mock(LifecycleInstallerService::class, [$this->filesystem])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $installer->shouldReceive('publishConfig')
            ->once()
            ->with(false, false)
            ->andReturn(['status' => 'success']);

        $installer->shouldReceive('createBaseAuthorizer')
            ->once()
            ->with(false, false)
            ->andReturn(['status' => 'success']);

        $installer->shouldReceive('createLifecycleMiddleware')
            ->once()
            ->with(false, false)
            ->andReturn(['status' => 'success']);

        $installer->shouldReceive('createServiceProvider')
            ->once()
            ->with(false, false)
            ->andReturn(['status' => 'success']);

        $installer->shouldReceive('registerServiceProvider')
            ->once()
            ->with(false, false)
            ->andReturn(['status' => 'success']);

        // Call the install method
        $results = $installer->install();

        // Assert the results contain all expected keys
        $this->assertArrayHasKey('config', $results);
        $this->assertArrayHasKey('authorizer', $results);
        $this->assertArrayHasKey('middleware', $results);
        $this->assertArrayHasKey('provider', $results);
        $this->assertArrayHasKey('provider_registration', $results);
    }
}
