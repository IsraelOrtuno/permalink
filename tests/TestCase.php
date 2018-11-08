<?php

namespace Devio\Permalink\Tests;

use Devio\Permalink\Permalink;
use Devio\Permalink\PermalinkServiceProvider;
use Arcanedev\SeoHelper\SeoHelperServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        // Reset maps
        Relation::morphMap([], false);
        Permalink::actionMap([], false);

        $this->loadLaravelMigrations('testing');
        $this->loadMigrationsFrom(__DIR__ . '/Support/migrations');
        $this->withFactories(__DIR__ . '/Support/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', Kernel::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            SeoHelperServiceProvider::class,
            PermalinkServiceProvider::class,
            \Cviebrock\EloquentSluggable\ServiceProvider::class
        ];
    }

    protected function reloadRoutes()
    {
        $this->app['router']->loadPermalinks();
    }
}