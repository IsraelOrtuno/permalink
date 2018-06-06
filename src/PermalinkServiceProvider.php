<?php

namespace Devio\Permalink;

use Illuminate\Routing\Route;
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

        $this->definePermalinkMacro();

        (new Router($this->app['router'], new ActionResolver))->load();
    }

    protected function definePermalinkMacro()
    {
        // Adding a permalink macro to the Route object will let us store a
        // permalink model instance right directly into the route, we can
        // then access to this instance from the current Route object.
        Route::macro('permalink', function ($permalink = null) {
            if (is_null($permalink)) {
                return $this->permalink;
            }

            $permalink->setRelations([]);

            $this->permalink = $permalink;
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {

    }
}