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
                            {--with-dummy : Install with dummy data}
                            {--f|force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install backend System';

    private $moduels = [];

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
        $this->call('vendor:publish', [
            '--provider' => 'Backend\BackendServiceProvider',
            '--tag' => 'config',
            '--force' => $this->option('force'),
        ]);
        $this->init();

        $this->publish();

        $this->info('Attempting to set Voyager User model as parent to App\User');
        if (file_exists(app_path('Models/User.php'))) {

            $userPath = app_path('Models/User.php');

            $str = file_get_contents($userPath);

            if ($str !== false) {
                $str = str_replace('extends Authenticatable', "extends \Backend\Models\User", $str);

                file_put_contents($userPath, $str);
            }
        } else {
            $this->warn('Unable to locate "User.php" in app/Models.  Did you move this file?');
            $this->warn('You will need to update this manually.  Change "extends Authenticatable" to "extends \TCG\Voyager\Models\User" in your User model');
        }

        $this->migrate_and_seed();

        // optimize application
        $this->call('optimize:clear');

        // setup installed flag in backend config
        Config::write('backend.installed', true);

        // clear
        $this->call('config:clear');

        if ($this->option('with-dummy')) {
            $this->allpublish();
        }

        // routes


        $this->info('Dumping the autoloaded files and reloading all new files');

        $composer = $this->findComposer();

        $process = new Process([$composer . ' dump-autoload']);
        $process->setTimeout(null); // Setting timeout to null to prevent installation from stopping at a certain point in time
        $process->setWorkingDirectory(base_path())->run();

        $this->info('Adding Backend routes to routes/web.php');
        $routes_contents = $filesystem->get(base_path('routes/web.php'));
        if (false === strpos($routes_contents, 'Backend::routes()')) {
            $filesystem->append(
                base_path('routes/web.php'),
                "\n\nRoute::group(['prefix' => 'backend'], function () {\n    \Backend\Support\Facades\Backend::routes();\n});\n"
            );
        }

        // $this->info('Adding Backend routes to routes/api.php');
        // $routes_contents = $filesystem->get(base_path('routes/api.php'));
        // if (false === strpos($routes_contents, 'Backend::api()')) {
        //     $filesystem->append(
        //         base_path('routes/api.php'),
        //         "\n\nRoute::group(['prefix' => 'backend'], function () {\n    \Backend\Support\Facades\Backend::api();\n});\n"
        //     );
        // }


        $this->info('Adding the storage symlink to your public folder');
        $this->call('storage:link');

        $this->info('Successfully installed DUCOR Backend! Enjoy');
    }


    private function allpublish()
    {
        $this->call('vendor:publish', [
            '--provider' => 'Backend\BackendServiceProvider',
            '--tag' => 'views',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'Backend\BackendServiceProvider',
            '--tag' => 'lang',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'Backend\BackendServiceProvider',
            '--tag' => 'migrations',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'Backend\BackendServiceProvider',
            '--tag' => 'seeders',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * init
     */
    public function init()
    {

        if (Config::get('backend.installed') == true && $this->option('force') == true) {
            $this->info("Application already reinstalling");
        }
        elseif (Config::get('backend.installed') == true && $this->option('force') == false) {
            $this->error("Application already installed");
            exit();
            die();
            return 0;
        }else{
            $this->info("Application already installing");
        }

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

        if (Config::get('backend.installed') == false && !$this->option('force')) {
            $this->error("Application allready installed");
        }
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
     * migrate and seeding
     */
    public function migrate_and_seed()
    {
        // migrate and seeder
        if (!$this->call('migrate')) {
            $this->call('migrate:fresh');
        }

        $this->call('db:seed', [
            '--class' => '\Backend\Seeders\BackendDatabaseSeeder'
        ]);
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