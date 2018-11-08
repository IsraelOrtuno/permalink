<?php

namespace Devio\Permalink\Tests\Feature;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\Support\Controllers\TestController;
use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Support\Models\User;

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
    public function it_can_read_raw_actions()
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
}