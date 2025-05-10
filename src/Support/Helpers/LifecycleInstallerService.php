<?php

namespace Kwidoo\Lifecycle\Support\Helpers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class LifecycleInstallerService
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new installer service instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Publish the configuration file.
     *
     * @param  bool  $force  Whether to overwrite existing files
     * @param  bool  $dryRun Whether to simulate the operation without making changes
     * @return array Operation results
     */
    public function publishConfig(bool $force = false, bool $dryRun = false): array
    {
        $sourcePath = __DIR__ . '/../../../config/config.php';
        $targetPath = config_path('lifecycle.php');

        return $this->publishFile($sourcePath, $targetPath, $force, $dryRun);
    }

    /**
     * Create a base authorizer class.
     *
     * @param  bool  $force  Whether to overwrite existing files
     * @param  bool  $dryRun Whether to simulate the operation without making changes
     * @return array Operation results
     */
    public function createBaseAuthorizer(bool $force = false, bool $dryRun = false): array
    {
        $directory = app_path('Authorizers');
        $filePath = $directory . '/DefaultAuthorizer.php';

        // Create the directory if it doesn't exist
        if (!$this->files->isDirectory($directory) && !$dryRun) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $stubPath = __DIR__ . '/../../Commands/stubs/authorizer.stub';
        $stub = $this->files->get($stubPath);

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            ['App\\Authorizers', 'DefaultAuthorizer'],
            $stub
        );

        return $this->writeFile($filePath, $content, $force, $dryRun);
    }

    /**
     * Create middleware for lifecycle integration.
     *
     * @param  bool  $force  Whether to overwrite existing files
     * @param  bool  $dryRun Whether to simulate the operation without making changes
     * @return array Operation results
     */
    public function createLifecycleMiddleware(bool $force = false, bool $dryRun = false): array
    {
        $directory = app_path('Http/Middleware');
        $filePath = $directory . '/LifecycleMiddleware.php';

        if (!$this->files->isDirectory($directory) && !$dryRun) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $content = <<<'EOT'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Factories\LifecycleMiddlewareFactory;

class LifecycleMiddleware
{
    /**
     * The lifecycle middleware factory instance.
     */
    protected LifecycleMiddlewareFactory $factory;

    /**
     * Create a new middleware instance.
     */
    public function __construct(LifecycleMiddlewareFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $resource
     * @param  string  $action
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $resource, string $action)
    {
        $options = new LifecycleOptionsData(
            $resource,
            $action,
            ['request' => $request],
            true, // events
            true, // transactions
            true, // logging
            true  // retries
        );

        $pipeline = $this->factory->createPipeline($options);

        return $pipeline->process($request, $next);
    }
}
EOT;

        return $this->writeFile($filePath, $content, $force, $dryRun);
    }

    /**
     * Create service provider for lifecycle integration.
     *
     * @param  bool  $force  Whether to overwrite existing files
     * @param  bool  $dryRun Whether to simulate the operation without making changes
     * @return array Operation results
     */
    public function createServiceProvider(bool $force = false, bool $dryRun = false): array
    {
        $directory = app_path('Providers');
        $filePath = $directory . '/LifecycleServiceProvider.php';

        if (!$this->files->isDirectory($directory) && !$dryRun) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $content = <<<'EOT'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Authorizers\DefaultAuthorizer;
use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;

class LifecycleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register custom authorizers
        $this->app->bind(Authorizer::class, DefaultAuthorizer::class);

        // You can also bind specific resource authorizers
        // $this->app->bind('lifecycle.authorizers.user', YourCustomUserAuthorizer::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
EOT;

        return $this->writeFile($filePath, $content, $force, $dryRun);
    }

    /**
     * Register the service provider in the application config.
     *
     * @param  bool  $force  Whether to overwrite existing entries
     * @param  bool  $dryRun Whether to simulate the operation without making changes
     * @return array Operation results
     */
    public function registerServiceProvider(bool $force = false, bool $dryRun = false): array
    {
        $configPath = config_path('app.php');

        if (!$this->files->exists($configPath)) {
            return [
                'status' => 'error',
                'message' => 'App configuration file not found.',
                'path' => $configPath
            ];
        }

        $content = $this->files->get($configPath);

        if (Str::contains($content, 'App\\Providers\\LifecycleServiceProvider::class') && !$force) {
            return [
                'status' => 'skipped',
                'message' => 'Service provider already registered.',
                'path' => $configPath
            ];
        }

        if ($dryRun) {
            return [
                'status' => 'simulated',
                'message' => 'Would register service provider in app config.',
                'path' => $configPath
            ];
        }

        // Find the providers array
        $pattern = "/('providers'\s*=>\s*\[\s*)([\s\S]*?)(\s*\])/m";
        $replacement = "$1$2\n        App\\Providers\\LifecycleServiceProvider::class,$3";

        $newContent = preg_replace($pattern, $replacement, $content);

        if ($newContent !== $content) {
            $this->files->put($configPath, $newContent);
            return [
                'status' => 'success',
                'message' => 'Service provider registered in app config.',
                'path' => $configPath
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Could not locate providers array in app config.',
            'path' => $configPath
        ];
    }

    /**
     * Run all installation steps.
     *
     * @param  bool  $force  Whether to overwrite existing files
     * @param  bool  $dryRun Whether to simulate the operation without making changes
     * @return array Operation results for each step
     */
    public function install(bool $force = false, bool $dryRun = false): array
    {
        return [
            'config' => $this->publishConfig($force, $dryRun),
            'authorizer' => $this->createBaseAuthorizer($force, $dryRun),
            'middleware' => $this->createLifecycleMiddleware($force, $dryRun),
            'provider' => $this->createServiceProvider($force, $dryRun),
            'provider_registration' => $this->registerServiceProvider($force, $dryRun)
        ];
    }

    /**
     * Publish a file from source to target path.
     *
     * @param  string  $sourcePath
     * @param  string  $targetPath
     * @param  bool  $force
     * @param  bool  $dryRun
     * @return array
     */
    protected function publishFile(string $sourcePath, string $targetPath, bool $force, bool $dryRun): array
    {
        if (!$this->files->exists($sourcePath)) {
            return [
                'status' => 'error',
                'message' => 'Source file does not exist.',
                'source' => $sourcePath,
                'target' => $targetPath
            ];
        }

        if ($this->files->exists($targetPath) && !$force) {
            return [
                'status' => 'skipped',
                'message' => 'Target file already exists.',
                'source' => $sourcePath,
                'target' => $targetPath
            ];
        }

        if ($dryRun) {
            return [
                'status' => 'simulated',
                'message' => 'Would publish file.',
                'source' => $sourcePath,
                'target' => $targetPath
            ];
        }

        // Create directory if needed
        $directory = dirname($targetPath);
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $this->files->copy($sourcePath, $targetPath);

        return [
            'status' => 'success',
            'message' => 'File published successfully.',
            'source' => $sourcePath,
            'target' => $targetPath
        ];
    }

    /**
     * Write content to a file.
     *
     * @param  string  $path
     * @param  string  $content
     * @param  bool  $force
     * @param  bool  $dryRun
     * @return array
     */
    protected function writeFile(string $path, string $content, bool $force, bool $dryRun): array
    {
        if ($this->files->exists($path) && !$force) {
            return [
                'status' => 'skipped',
                'message' => 'File already exists.',
                'path' => $path
            ];
        }

        if ($dryRun) {
            return [
                'status' => 'simulated',
                'message' => 'Would write file.',
                'path' => $path
            ];
        }

        // Create directory if needed
        $directory = dirname($path);
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $this->files->put($path, $content);

        return [
            'status' => 'success',
            'message' => 'File created successfully.',
            'path' => $path
        ];
    }
}
