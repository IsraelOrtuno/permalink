<?php

namespace Devio\Permalink;

use Illuminate\Routing\Route;
use Devio\Permalink\Routing\Router;
use Devio\Permalink\Contracts\Manager;
use Illuminate\Support\ServiceProvider;
use Arcanedev\SeoHelper\Contracts\SeoHelper;
use Devio\Permalink\Contracts\Router as PermalinkRouter;

class PermalinkServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permalink.php', 'permalink');

        $this->publishes([__DIR__ . '/../config/permalink.php' => config_path('permalink.php')], 'permalink-config');

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
            'base'      => \Devio\Permalink\Builders\BaseBuilder::class,
            'meta'      => \Devio\Permalink\Builders\MetaBuilder::class,
            'opengraph' => \Devio\Permalink\Builders\OpenGraphBuilder::class,
            'twitter'   => \Devio\Permalink\Builders\TwitterBuilder::class,
        ];

        foreach ($builders as $alias => $builder) {
            $this->app->singleton("permalink.$alias", function ($app, $parameters) use ($builder) {
                $helper = $app->make(SeoHelper::class);

                return (new $builder($helper))
                    ->permalink($parameters[0])
                    ->data($parameters[1]);
            });
        }

        $this->app->singleton(Manager::class, function () {
            return new PermalinkManager($this->app['request'], $this->app);
        });

        $this->app->singleton(PermalinkRouter::class, function () {
            return new Router($this->app['router']);
        });

        $this->app->alias(PermalinkRouter::class, 'permalink');
    }
}