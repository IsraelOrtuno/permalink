<?php

namespace Devio\Permalink\Tests\Feature\Routing;

use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Support\Models\User;

class ResolvingTest extends TestCase
{
    /** @test */
    public function it_can_resolve_route_using_resource()
    {
        $user = factory(User::class)->create(['name' => 'israel ortuno']);

        $this->assertEquals('http://localhost/israel-ortuno', permalink($user));
    }

    /** @test */
    public function it_can_resolve_route_using_integer()
    {
        factory(User::class)->create(['name' => 'israel ortuno']);

        $this->assertEquals('http://localhost/israel-ortuno', permalink(1));
    }

    /** @test */
    public function it_can_resolve_route_using_permalink()
    {
        $user = factory(User::class)->create(['name' => 'israel ortuno']);

        $this->assertEquals('http://localhost/israel-ortuno', permalink($user->permalink));
    }
}