<?php

namespace Devio\Permalink\Tests\Unit;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Services\ActionFactory;

class ActionFactoryTest extends TestCase
{
    /** @test */
    public function it_will_resolve_the_controller_view_if_action_is_a_view()
    {
        $factory = new ActionFactory;
        $permalink = Permalink::create(['slug' => 'foo', 'action' => 'welcome']);

        $this->assertEquals('Devio\Permalink\Http\PermalinkController@view', $factory->resolve($permalink));
    }
}
