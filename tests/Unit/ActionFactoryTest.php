<?php

namespace Devio\Permalink\Tests\Unit;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Services\ActionFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Devio\Permalink\Tests\Support\Controllers\TestController;

class ActionFactoryTest extends TestCase
{
    /** @test */
    public function it_will_resolve_the_controller_view_if_action_is_a_view()
    {
        $factory = new ActionFactory;
        $permalink = Permalink::create(['slug' => 'foo', 'action' => 'welcome']);

        $this->assertEquals('Devio\Permalink\Http\PermalinkController@view', $factory->resolve($permalink));
    }

    /** @test */
    public function it_fails_if_action_is_null()
    {
        $factory = new ActionFactory;
        $permalink = Permalink::create(['slug' => 'foo', 'action' => null]);

        $this->expectException(HttpException::class);
        $factory->resolve($permalink);
    }

    /** @test */
    public function it_fails_if_action_does_not_exist()
    {
        $factory = new ActionFactory;
        $permalink = Permalink::create(['slug' => 'foo', 'action' => 'nonexisting']);

        $this->expectException(HttpException::class);
        $factory->resolve($permalink);
    }

    /** @test */
    public function it_fails_if_the_controller_action_is_not_callable()
    {
        $factory = new ActionFactory;
        $permalink = Permalink::create(['slug' => 'foo', 'action' => TestController::class . '@nonexisting']);

        $this->expectException(HttpException::class);
        $factory->resolve($permalink);
    }
}
