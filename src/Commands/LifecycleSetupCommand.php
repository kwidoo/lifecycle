<?php

namespace Kwidoo\Lifecycle\Commands;

use Illuminate\Console\Command;
use Kwidoo\Lifecycle\Support\Helpers\LifecycleInstallerService;

class LifecycleSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lifecycle:setup
                            {--force : Overwrite any existing files}
                            {--dry-run : Simulate the installation without making any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the Lifecycle package in your Laravel application';

    /**
     * The installer service instance.
     *
     * @var \Kwidoo\Lifecycle\Support\Helpers\LifecycleInstallerService
     */
    protected $installer;

    /**
     * Create a new command instance.
     *
     * @param  \Kwidoo\Lifecycle\Support\Helpers\LifecycleInstallerService  $installer
     * @return void
     */
    public function __construct(LifecycleInstallerService $installer)
    {
        parent::__construct();
        $this->installer = $installer;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in dry-run mode. No files will be changed.');
        }

        $this->info('Setting up Lifecycle package...');

        // Check if user wants to continue with force option
        if ($force && !$dryRun && !$this->confirm('The --force option will overwrite existing files. Do you want to continue?')) {
            $this->info('Setup aborted.');
            return 0;
        }

        // Run the installation
        $results = $this->installer->install($force, $dryRun);

        // Display results
        $this->displayResults($results, $dryRun);

        $this->info('');

        if ($dryRun) {
            $this->info('Dry run complete. Run without --dry-run to apply changes.');
        } else {
            $this->info('Lifecycle setup completed successfully.');
            $this->displayNextSteps();
        }

        return 0;
    }

    /**
     * Display the installation results.
     *
     * @param  array  $results
     * @param  bool   $dryRun
     * @return void
     */
    protected function displayResults(array $results, bool $dryRun)
    {
        $this->info('');
        $this->info('Installation Results:');

        foreach ($results as $step => $result) {
            $this->info('');
            $this->info("[$step]:");

            switch ($result['status']) {
                case 'success':
                    $this->info("  ✓ " . $result['message']);
                    $this->line("    <comment>Path:</comment> " . ($result['path'] ?? 'N/A'));
                    break;

                case 'skipped':
                    $this->line("  <comment>⚠ " . $result['message'] . "</comment>");
                    $this->line("    <comment>Path:</comment> " . ($result['path'] ?? 'N/A'));
                    break;

                case 'simulated':
                    $this->line("  <info>▶ " . $result['message'] . "</info>");
                    $this->line("    <comment>Path:</comment> " . ($result['path'] ?? 'N/A'));
                    break;

                case 'error':
                    $this->error("  ✕ " . $result['message']);
                    $this->line("    <comment>Path:</comment> " . ($result['path'] ?? 'N/A'));
                    break;

                default:
                    $this->line("  • Unknown status: " . json_encode($result));
            }
        }
    }

    /**
     * Display next steps for the user after installation.
     *
     * @return void
     */
    protected function displayNextSteps()
    {
        $this->info('');
        $this->info('Next Steps:');
        $this->line('');
        $this->line('  1. <comment>Review the configuration file:</comment> config/lifecycle.php');
        $this->line('  2. <comment>Register the middleware in app/Http/Kernel.php:</comment>');
        $this->line('');
        $this->line('     <info>protected $routeMiddleware = [</info>');
        $this->line('         <info>// ... other middleware</info>');
        $this->line('         <info>\'lifecycle\' => \App\Http\Middleware\LifecycleMiddleware::class,</info>');
        $this->line('     <info>];</info>');
        $this->line('');
        $this->line('  3. <comment>Use the middleware in your routes:</comment>');
        $this->line('');
        $this->line('     <info>Route::middleware([\'lifecycle:users,view\'])->get(\'/users\', function () {</info>');
        $this->line('         <info>// Your controller logic here</info>');
        $this->line('     <info>});</info>');
        $this->line('');
        $this->line('  4. <comment>Create custom authorizers as needed:</comment>');
        $this->line('');
        $this->line('     <info>php artisan make:authorizer User</info>');
        $this->line('');
        $this->line('  5. <comment>Register your custom authorizers in App\\Providers\\LifecycleServiceProvider</comment>');
    }
}
