<?php

namespace Kwidoo\Lifecycle\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeFlowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lifecycle:make-flow {name : The name of the flow}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new flow with associated files (strategy, feature, middleware)';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Standardize the name format (e.g., "cache" -> "Cache")
        $name = ucfirst($name);

        $this->createStrategyInterface($name);
        $this->createFeatureInterface($name);
        $this->createFeatureImplementation($name);
        $this->createStrategyImplementation($name);
        $this->createMiddleware($name);

        $this->info("$name flow files have been created successfully!");
        return 0;
    }

    /**
     * Create strategy interface file.
     *
     * @param  string  $name
     * @return void
     */
    protected function createStrategyInterface(string $name)
    {
        $path = base_path('src/Contracts/Strategies/' . $name . 'Strategy.php');
        $this->makeDirectory(dirname($path));

        $content = $this->getStrategyInterfaceStub($name);
        $this->files->put($path, $content);

        $this->info("Created Strategy Interface: {$name}Strategy.php");
    }

    /**
     * Create feature interface file.
     *
     * @param  string  $name
     * @return void
     */
    protected function createFeatureInterface(string $name)
    {
        $path = base_path('src/Contracts/Features/' . $this->getInterfaceName($name) . '.php');
        $this->makeDirectory(dirname($path));

        $content = $this->getFeatureInterfaceStub($name);
        $this->files->put($path, $content);

        $this->info("Created Feature Interface: {$this->getInterfaceName($name)}.php");
    }

    /**
     * Create feature implementation file.
     *
     * @param  string  $name
     * @return void
     */
    protected function createFeatureImplementation(string $name)
    {
        $path = base_path('src/Features/' . $name . '/Default' . $this->getInterfaceName($name) . '.php');
        $this->makeDirectory(dirname($path));

        $content = $this->getFeatureImplementationStub($name);
        $this->files->put($path, $content);

        $this->info("Created Feature Implementation: Default{$this->getInterfaceName($name)}.php");
    }

    /**
     * Create strategy implementation file.
     *
     * @param  string  $name
     * @return void
     */
    protected function createStrategyImplementation(string $name)
    {
        $path = base_path('src/Strategies/' . $name . '/Default' . $name . 'Strategy.php');
        $this->makeDirectory(dirname($path));

        $content = $this->getStrategyImplementationStub($name);
        $this->files->put($path, $content);

        $this->info("Created Strategy Implementation: Default{$name}Strategy.php");
    }

    /**
     * Create middleware file.
     *
     * @param  string  $name
     * @return void
     */
    protected function createMiddleware(string $name)
    {
        $path = base_path('src/Middleware/' . $name . 'sMiddleware.php');
        $this->makeDirectory(dirname($path));

        $content = $this->getMiddlewareStub($name);
        $this->files->put($path, $content);

        $this->info("Created Middleware: {$name}sMiddleware.php");
    }

    /**
     * Get the stub content for the strategy interface.
     *
     * @param  string  $name
     * @return string
     */
    protected function getStrategyInterfaceStub(string $name)
    {
        $stub = <<<'EOF'
<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

interface {name}Strategy
{
    /**
     * Execute with {nameLower} handling
     *
     * @param callable $callback
     * @return mixed
     */
    public function execute(callable $callback): mixed;
}
EOF;

        return str_replace(
            ['{name}', '{nameLower}'],
            [$name, strtolower($name)],
            $stub
        );
    }

    /**
     * Get the stub content for the feature interface.
     *
     * @param  string  $name
     * @return string
     */
    protected function getFeatureInterfaceStub(string $name)
    {
        $interfaceName = $this->getInterfaceName($name);

        $stub = <<<'EOF'
<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Closure;

interface {interfaceName}
{
    /**
     * Execute a callback with {nameLower} handling
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(Closure $callback): mixed;
}
EOF;

        return str_replace(
            ['{interfaceName}', '{nameLower}'],
            [$interfaceName, strtolower($name)],
            $stub
        );
    }

    /**
     * Get the stub content for the feature implementation.
     *
     * @param  string  $name
     * @return string
     */
    protected function getFeatureImplementationStub(string $name)
    {
        $interfaceName = $this->getInterfaceName($name);

        $stub = <<<'EOF'
<?php

namespace Kwidoo\Lifecycle\Features\{name};

use Closure;
use Kwidoo\Lifecycle\Contracts\Features\{interfaceName};

class Default{interfaceName} implements {interfaceName}
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Add required dependencies here
    }

    /**
     * Execute a callback with {nameLower} handling
     *
     * @param callable $callback
     * @return mixed
     */
    public function execute(callable $callback): mixed
    {
        // Implement {nameLower} logic here
        return $callback();
    }
}
EOF;

        return str_replace(
            ['{name}', '{interfaceName}', '{nameLower}'],
            [$name, $interfaceName, strtolower($name)],
            $stub
        );
    }

    /**
     * Get the stub content for the strategy implementation.
     *
     * @param  string  $name
     * @return string
     */
    protected function getStrategyImplementationStub(string $name)
    {
        $interfaceName = $this->getInterfaceName($name);

        $stub = <<<'EOF'
<?php

namespace Kwidoo\Lifecycle\Strategies\{name};

use Closure;
use Kwidoo\Lifecycle\Contracts\Features\{interfaceName};
use Kwidoo\Lifecycle\Contracts\Strategies\{name}Strategy;

class Default{name}Strategy implements {name}Strategy
{
    /**
     * @param {interfaceName} ${nameCamel}
     */
    public function __construct(
        protected {interfaceName} ${nameCamel}
    ) {}

    /**
     * Execute with {nameLower} handling
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(callable $callback): mixed
    {
        return $this->{nameCamel}->execute($callback);
    }
}
EOF;

        return str_replace(
            ['{name}', '{interfaceName}', '{nameLower}', '{nameCamel}'],
            [$name, $interfaceName, strtolower($name), lcfirst($interfaceName)],
            $stub
        );
    }

    /**
     * Get the stub content for the middleware.
     *
     * @param  string  $name
     * @return string
     */
    protected function getMiddlewareStub(string $name)
    {
        $stub = <<<'EOF'
<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\{name}Strategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

class {name}sMiddleware
{
    /**
     * @param {name}Strategy ${nameLower}Strategy
     */
    public function __construct(
        protected {name}Strategy ${nameLower}Strategy
    ) {}

    /**
     * Handle the lifecycle request with {nameLower} handling
     *
     * @param LifecycleContextData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData $data, Closure $next): mixed
    {
        return $this->{nameLower}Strategy
            ->execute(
                fn() => $next($data)
            );
    }
}
EOF;

        return str_replace(
            ['{name}', '{nameLower}'],
            [$name, lcfirst($name)],
            $stub
        );
    }

    /**
     * Get the interface name from the flow name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getInterfaceName(string $name)
    {
        return $name . 'able';
    }

    /**
     * Create the directory for the file if it doesn't exist.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }

        return $path;
    }
}
