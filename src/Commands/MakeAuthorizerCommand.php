<?php

namespace Kwidoo\Lifecycle\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeAuthorizerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:authorizer
                            {name : The name of the authorizer}
                            {--path= : The path where the authorizer should be created}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new authorizer class';

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
        $name = Str::studly($name);

        if (!Str::endsWith($name, 'Authorizer')) {
            $name .= 'Authorizer';
        }

        $customPath = $this->option('path');
        if ($customPath) {
            $path = rtrim($customPath, '/') . '/' . $name . '.php';
            $namespace = $this->getNamespaceFromPath($customPath);
        } else {
            $path = app_path('Authorizers/' . $name . '.php');
            $namespace = 'App\\Authorizers';
        }

        // Create the directory if it doesn't exist
        $directory = dirname($path);
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($this->files->exists($path)) {
            $this->error("Authorizer {$name} already exists!");
            return 1;
        }

        $this->makeAuthorizer($name, $path, $namespace);

        $this->info("✓ Authorizer created successfully.");
        $this->line("  <info>Authorizer class:</info> {$namespace}\\{$name}");
        $this->line("  <info>File location:</info> {$path}");

        $this->line("\n<comment>Next steps:</comment>");
        $this->line("  - Implement your authorization logic in the authorize() method");
        $this->line("  - Register your authorizer in a service provider if needed");
        $this->line("  - Use the DefaultAuthorizerFactory to resolve your authorizer");

        return 0;
    }

    /**
     * Create a new authorizer file at the given path.
     *
     * @param  string  $name Class name
     * @param  string  $path File path
     * @param  string  $namespace Class namespace
     * @return void
     */
    protected function makeAuthorizer($name, $path, $namespace)
    {
        $stub = $this->files->get($this->getStubPath());

        $stub = str_replace('{{ namespace }}', $namespace, $stub);
        $stub = str_replace('{{ class }}', $name, $stub);

        $this->files->put($path, $stub);
    }

    /**
     * Get the stub file path.
     *
     * @return string
     */
    protected function getStubPath()
    {
        return __DIR__ . '/stubs/authorizer.stub';
    }

    /**
     * Determine namespace from a custom path.
     *
     * @param string $path
     * @return string
     */
    protected function getNamespaceFromPath($path)
    {
        // Try to derive namespace from path, assuming PSR-4 structure
        // Default to App\Authorizers if we can't determine one
        if (Str::startsWith($path, 'app/')) {
            return 'App\\' . str_replace('/', '\\', Str::after($path, 'app/'));
        }

        if (Str::startsWith($path, 'src/')) {
            return 'Kwidoo\\Lifecycle\\' . str_replace('/', '\\', Str::after($path, 'src/'));
        }

        return 'App\\Authorizers';
    }
}
