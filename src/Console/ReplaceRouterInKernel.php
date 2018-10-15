<?php

namespace Devio\Permalink\Console;

use Illuminate\Console\Command;

class ReplaceRouterInKernel extends Command
{
    public $signature = 'permalink:replace-router';

    public $description = 'Replace the default Laravel Router in the default Http\Kernel';

    public function handle()
    {
        $kernel = file_get_contents(app_path('Http/Kernel.php'));

        if (strpos($kernel, 'ReplacesRouter') !== false) {
            $this->error('The Kernel is already using the ReplacesRouter trait.');
            return;
        }

        $from = '/' . preg_quote('{', '/') . '/';

        $to = '{'  . PHP_EOL . '    use \Devio\Permalink\Routing\ReplacesRouter;' . PHP_EOL;

        file_put_contents(
            app_path('Http/Kernel.php'),
            preg_replace($from, $to, $kernel, 1)
        );
    }
}