<?php

namespace Devio\Permalink\Tests;

use Devio\Permalink\Contracts\SeoBuilder;
use Devio\Permalink\Meta\Builder\Builder;
use Devio\Permalink\Permalink;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Mockery as m;
use Devio\Permalink\Middleware\BuildSeo;

class MiddlewareTest extends TestCase
{
    /** @test */
    public function call_seo_builders_if_present()
    {
        list($request, $route, $builder) = $this->prepareMocks(['seo' => ['meta' => 'foo']]);

        $builder->shouldReceive('build')->with('meta', 'foo')->once();

        $this->handleMiddleware($request);
    }

    /** @test */
    public function do_not_call_builder_if_null()
    {
        list($request, $route, $builder) = $this->prepareMocks(['seo' => ['meta' => null]]);

        $builder->shouldNotReceive('build');

        $this->handleMiddleware($request);
    }

    /** @test */
    public function call_all_builders_from_seo_attribute()
    {
        list($request, $route, $builder) = $this->prepareMocks(['seo' => ['meta' => 'foo', 'opengraph' => 'bar']]);

        $builder->shouldReceive('build')->with('meta', 'foo')->once();
        $builder->shouldReceive('build')->with('opengraph', 'bar')->once();

        $this->handleMiddleware($request);
    }

    protected function handleMiddleware($request)
    {
        (new BuildSeo)->handle($request, function ($request) {
        });
    }

    protected function prepareMocks($permalink)
    {
        $route = m::mock(Route::class);
        $request = m::mock(Request::class);
        $builder = m::mock(SeoBuilder::class);

        $route->shouldReceive('permalink')->once()->andReturn(new Permalink($permalink));
        $request->shouldReceive('route')->once()->andReturn($route);

        $this->app->singleton('permalink.meta', function () use ($builder) {
            return $builder;
        });

        $this->app->singleton('permalink.opengraph', function () use ($builder) {
            return $builder;
        });

        return [$request, $route, $builder];
    }
}