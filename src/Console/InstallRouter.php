<?php

namespace Devio\Permalink\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallRouter extends Command
{
    public $signature = 'permalink:install {--default}';

    public $description = 'Replace the default Laravel Router';

    public function handle()
    {
        if ($this->option('default') == true) {
            return $this->inKernel();
        }
        
        $choice = $this->choice('Where do you want to replace the router?', ['Http/Kernel.php (Recommended)', 'bootstrap/app.php (Advanced)'], 0);

        Str::contains($choice, 'bootstrap') ?
            $this->inBootstrap() : $this->inKernel();
    }

    public function inBootstrap()
    {
        $app = file_get_contents(base_path('bootstrap/app.php'));

        if (strpos($app, '\Devio\Permalink\Routing\Router::class') !== false) {
            $this->error('The router class has already been replaced.');
            return;
        }

        $from = '/' . preg_quote('$app->singleton', '/') . '/';
        $to = '$app->singleton(\'router\', \Devio\Permalink\Routing\Router::class);' . PHP_EOL . PHP_EOL . '$app->singleton';

        file_put_contents(
            base_path('bootstrap/app.php'),
            preg_replace($from, $to, $app, 1)
        );
    }

    public function inKernel()
    {
        $kernel = file_get_contents(app_path('Http/Kernel.php'));

        if (strpos($kernel, 'ReplacesRouter') !== false) {
            $this->error('The Kernel is already using the ReplacesRouter trait.');
            return;
        }

        $from = '/' . preg_quote('{', '/') . '/';
        $to = '{' . PHP_EOL . '    use \Devio\Permalink\Routing\ReplacesRouter;' . PHP_EOL;

        file_put_contents(
            app_path('Http/Kernel.php'),
            preg_replace($from, $to, $kernel, 1)
        );
    }
}