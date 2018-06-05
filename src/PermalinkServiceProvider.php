<?php

namespace Devio\Permalink;

use Devio\Permalink\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Devio\Permalink\Routing\ActionResolver;

class PermalinkServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

//        (new Router($this->app['router'], new ActionResolver))->load();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {

    }
}