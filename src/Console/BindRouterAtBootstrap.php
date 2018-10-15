<?php

namespace Devio\Permalink\Console;

use Illuminate\Console\Command;

class BindRouterAtBootstrap extends Command
{
    public $signature = 'permalink:bind-router';

    public $description = 'Replace the default Laravel Router before bootstrapping the app (bootstrap/app.php)';

    public function handle()
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
}