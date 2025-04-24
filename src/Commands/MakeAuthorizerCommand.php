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
    protected $signature = 'make:authorizer {name : The name of the authorizer}';

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

        $path = app_path('Authorizers/' . $name . '.php');

        // Create the directory if it doesn't exist
        $directory = dirname($path);
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($this->files->exists($path)) {
            $this->error("Authorizer {$name} already exists!");
            return 1;
        }

        $this->makeAuthorizer($name, $path);

        $this->info("Authorizer {$name} created successfully.");

        return 0;
    }

    /**
     * Create a new authorizer file at the given path.
     *
     * @param  string  $name
     * @param  string  $path
     * @return void
     */
    protected function makeAuthorizer($name, $path)
    {
        $stub = $this->getStub();
        $contents = $this->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);

        $this->files->put($path, $contents);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = <<<'EOT'
<?php

namespace App\Authorizers;

use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;
use Spatie\LaravelData\Contracts\BaseData;

class DummyClass implements Authorizer
{
    /**
     * @param string $ability
     * @param BaseData|null $context
     *
     * @return void
     */
    public function authorize(string $ability, ?BaseData $context = null): void
    {
        // Implement your authorization logic here
    }
}
EOT;
        return $stub;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace'],
            ['App\\Authorizers', 'App\\'],
            $stub
        );

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        return str_replace('DummyClass', $name, $stub);
    }
}
