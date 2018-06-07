<?php

namespace Devio\Permalink\Commands;

use Illuminate\Console\Command;

class PermalinkResourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permalink:resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy the permalink package resources';

    public function handle()
    {
        if (! $this->confirm('Add this package path to your webpack.mix.js? Proceed manually if you thing this is too risky.')) {
            return;
        }

        $mix = file_get_contents(base_path('webpack.mix.js'));

        if (str_contains($mix, 'vendor/devio/permalink/resources/assets/js')) {
            $this->comment('This package path was already in your webpack.mix.js!');
            return;
        }

        $mix .= <<<EOL


if (!mix.config.webpackConfig.resolve) {
    mix.config.webpackConfig.resolve = {modules: []}
} else if (!mix.config.webpackConfig.resolve.modules) {
    mix.config.webpackConfig.resolve.modules = []
}

mix.config.webpackConfig.resolve.modules.push(
    path.resolve(__dirname, 'vendor/devio/permalink/resources/assets/js'),
)
EOL;
        if (! str_contains($mix, 'var path')) {
            $mix = "var path = require('path')\n" . $mix;
        }

        file_put_contents(base_path('webpack.mix.js'), $mix);

        $this->info('Package path added to your webpack.mix.js. Feel free to run your build!');
    }
}