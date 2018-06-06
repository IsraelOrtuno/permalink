<?php

namespace Devio\Permalink;

use Illuminate\Routing\Route;
use Devio\Permalink\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Devio\Permalink\Routing\ActionResolver;
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

        (new Router($this->app['router'], new ActionResolver))->load();
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
            'meta'      => \Devio\Permalink\Meta\Builder\MetaBuilder::class,
            'opengraph' => \Devio\Permalink\Meta\Builder\OpenGraphBuilder::class,
            'twitter'   => \Devio\Permalink\Meta\Builder\TwitterBuilder::class,
        ];

        foreach ($builders as $alias => $builder) {
            $this->app->singleton("permalink.$alias", function ($app) use ($builder) {
                return (new $builder($app->make(SeoHelper::class)));
            });
        }
    }
}