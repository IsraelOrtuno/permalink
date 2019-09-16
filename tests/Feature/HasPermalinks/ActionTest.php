<?php

namespace Devio\Permalink\Tests\Feature\HasPermalinks;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Support\Models\User;
use Devio\Permalink\Tests\Support\Controllers\TestController;

class ActionTest extends TestCase
{
    /** @test */
    public function it_gets_action_from_resource()
    {
        $user = factory(User::class)->create();

        $this->assertEquals($user->permalinkAction(), $user->permalink->action);
    }

    /** @test */
    public function it_can_map_actions()
    {
        $action = TestController::class . '@index';
        Permalink::actionMap(['user.index' => $action]);
        $permalink = Permalink::create(['slug' => 'foo', 'action' => $action]);

        $this->assertEquals($action, $permalink->action);
    }

    /** @test */
    public function it_reads_raw_actions()
    {
        Permalink::actionMap(['user.index' => TestController::class . '@index']);
        $permalink = Permalink::create(['slug' => 'foo', 'action' => 'user.index']);

        $this->assertEquals('user.index', $permalink->rawAction);
    }

    /** @test */
    public function it_maps_actions_before_saving()
    {
        Permalink::actionMap(['user.index' => TestController::class . '@index']);
        $permalink = Permalink::create(['slug' => 'foo', 'action' => TestController::class . '@index']);

        $this->assertEquals('user.index', $permalink->rawAction);
    }

    /** @test */
    public function it_can_override_default_actions()
    {
        $user = factory(User::class)->create([
            'permalink' => [
                'slug' => 'foo',
                'action' => \Devio\Permalink\Tests\Support\Controllers\TestController::class . '@override'
            ]
        ]);

        $this->get('foo')->assertSee('override');
    }

    /** @test */
    public function it_supports_view_paths_as_actions()
    {
        Permalink::create(['slug' => 'foo', 'action' => 'welcome']);

        $this->get('foo')->assertViewIs('welcome');
    }

    /** @test */
    public function it_will_pass_the_entity_to_the_view()
    {
        $user = factory(User::class)->create([
            'permalink' => [
                'slug'   => 'foo',
                'action' => 'welcome'
            ]
        ]);

        $response = $this->get('foo');
        $response->assertViewHas('user');

        $this->assertEquals($user->getKey(), $response->viewData('user')->getKey());
    }
}
