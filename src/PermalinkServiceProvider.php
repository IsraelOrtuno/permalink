<?php

namespace Devio\Permalink;

use Illuminate\Routing\Route;
use Devio\Permalink\Routing\Router;
use Devio\Permalink\Contracts\Manager;
use Illuminate\Support\ServiceProvider;
use Arcanedev\SeoHelper\Contracts\SeoHelper;

class PermalinkServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->definePermalinkMacro();
    }

    /**
     * Create the permalink macro.
     */
    protected function definePermalinkMacro()
    {
        // Adding a permalink macro to the Route object will let us store a
        // permalink model instance right directly into the route, we can
        // then access to this instance from the current Route object.
        Route::macro('permalink', function ($permalink = null) {
            if (is_null($permalink)) {
                return $this->permalink;
            }

            $this->permalink = $permalink->setRelations([]);
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $builders = [
            'meta'      => \Devio\Permalink\Builders\MetaBuilder::class,
            'opengraph' => \Devio\Permalink\Builders\OpenGraphBuilder::class,
            'twitter'   => \Devio\Permalink\Builders\TwitterBuilder::class,
        ];

        foreach ($builders as $alias => $builder) {
            $this->app->singleton("permalink.$alias", function ($app) use ($builder) {
                return (new $builder($app->make(SeoHelper::class)));
            });
        }

        $this->app->singleton(Manager::class, function() {
            return new PermalinkManager($this->app['request'], $this->app);
        });

        $this->app->singleton(\Devio\Permalink\Contracts\Router::class, function () {
            return new Router($this->app['router']);
        });

        $this->app->alias(\Devio\Permalink\Contracts\Router::class, 'permalink');
    }
}