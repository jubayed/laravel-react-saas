<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Illuminate\Console\ConfirmableTrait;

class InstallCommand extends Command
{

    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backend:install
                            {--f|force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install backend System';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Filesystem $filesystem)
    {
        $this->init();

        $this->publish();

        $this->migrate_and_seed();

        // optimize application
        $this->call('optimize:clear');
        // clear
        $this->call('config:clear');

        $this->info('Dumping the autoloaded files and reloading all new files');

        $composer = $this->findComposer();

        $process = new Process([$composer . ' dump-autoload']);
        $process->setTimeout(null); // Setting timeout to null to prevent installation from stopping at a certain point in time
        $process->setWorkingDirectory(base_path())->run();

        $this->info('Adding the storage symlink to your public folder');
        $this->call('storage:link');

        $this->info('Successfully installed DUCOR Backend! Enjoy');
    }


    /**
     * init
     */
    public function init()
    {

        // setup .env [APP_KEY]
        if (!file_exists(base_path('.env'))) {
            $this->error(".env file not found");

            if (!file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), base_path('.env'));
            } else {
                $this->error(".env.example file not found");
                exit();
            }
            $this->call('key:generate');
        }

        // create configs
        $this->call('config:clear');
    }

    /**
     * publish common
     */
    public function publish()
    {

        //spatie/laravel-activitylog
        $this->call('vendor:publish', [
            '--provider' => "Spatie\Activitylog\ActivitylogServiceProvider",
            '--tag' => 'config'
        ]);

        //Spatie\Analytics
        $this->call('vendor:publish', [
            '--provider' => "Spatie\Analytics\AnalyticsServiceProvider"
        ]);

        //Spatie\Backup
        $this->call('vendor:publish', [
            '--provider' => "Spatie\Backup\BackupServiceProvider"
        ]);

        //Spatie\Permission
        $this->call('vendor:publish', [
            '--provider' => "Spatie\Permission\PermissionServiceProvider",
            '--tag' => 'config',
            '--force' => true
        ]);

        //Biscolab\ReCaptcha
        $this->call('vendor:publish', [
            '--provider' => "Biscolab\ReCaptcha\ReCaptchaServiceProvider",
        ]);

        //Jubayed\Notify
        $this->call('vendor:publish', [
            '--provider' => "Jubayed\Notify\NotifyServiceProvider",
        ]);

        //spatie/laravel-activitylog

    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {

        if (file_exists(getcwd() . '/composer.phar')) {
            return '"' . PHP_BINARY . '" ' . getcwd() . '/composer.phar';
        }

        return 'composer';
    }
}