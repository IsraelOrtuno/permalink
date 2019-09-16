<?php

namespace Devio\Permalink;

use Illuminate\Support\Arr;
use Devio\Permalink\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Devio\Permalink\Contracts\PathBuilder;
use Devio\Permalink\Contracts\ActionFactory;
use Arcanedev\SeoHelper\Contracts\SeoHelper;

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

        $this->defineMacros();
    }

    /**
     * Create the permalink macro.
     */
    protected function defineMacros()
    {
        \Illuminate\Routing\Router::macro('replaceMiddleware', function ($middleware = [], $middlewareGroups = []) {
            $this->middleware = $middleware;
            $this->middlewareGroups = $middlewareGroups;
        });

        Arr::macro('undot', function (array $dotArray) {
            $array = [];
            foreach ($dotArray as $key => $value) {
                Arr::set($array, $key, $value);
            }

            return $array;
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $builders = [
            'base'      => Builders\BaseBuilder::class,
            'meta'      => Builders\MetaBuilder::class,
            'opengraph' => Builders\OpenGraphBuilder::class,
            'twitter'   => Builders\TwitterBuilder::class,
        ];

        foreach ($builders as $alias => $builder) {
            $this->app->singleton("permalink.$alias", function ($app, $parameters) use ($builder) {
                $helper = $app->make(SeoHelper::class);

                return (new $builder($helper))
                    ->permalink($parameters[0])
                    ->data($parameters[1]);
            });
        }

        $this->app->singleton('router', Router::class);

        $this->app->singleton(PermalinkSeo::class, function () {
            return new PermalinkSeo($this->app['request'], $this->app);
        });

        $this->app->singleton(PermalinkManager::class, function () {
            return new PermalinkManager;
        });

        $this->app->singleton(PathBuilder::class, function() {
            return new Services\PathBuilder;
        });

        $this->app->singleton(ActionFactory::class, function () {
            return new Services\ActionFactory;
        });

        $this->commands([
            Console\InstallRouter::class,
        ]);
    }
}
