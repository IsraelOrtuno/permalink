<?php

namespace Devio\Permalink;

use Devio\Permalink\Routing\ActionResolver;
use Devio\Permalink\Routing\Router;
use Illuminate\Support\ServiceProvider;

class PermalinkServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        (new Router($this->app['router'], new ActionResolver))->load();
    }

    public function register()
    {

    }
}