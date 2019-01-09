<?php

namespace Devio\Permalink;

use Devio\Permalink\Routing\Router;
use Illuminate\Support\ServiceProvider;
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

        $this->defineRouterMacro();
    }

    /**
     * Create the permalink macro.
     */
    protected function defineRouterMacro()
    {
        \Illuminate\Routing\Router::macro('replaceMiddleware', function ($middleware = [], $middlewareGroups = []) {
            $this->middleware = $middleware;
            $this->middlewareGroups = $middlewareGroups;
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

        $this->app->singleton(Contracts\RequestHandler::class, function () {
            return new RequestHandler($this->app['request'], $this->app);
        });

        $this->commands(Console\BindRouterInBootstrap::class);
        $this->commands(Console\ReplaceRouterInKernel::class);
    }
}