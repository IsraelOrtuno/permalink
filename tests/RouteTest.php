<?php

namespace Devio\Permalink\Tests;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\Dummy\DummyController;
use Devio\Permalink\Tests\Dummy\DummyUser;

class RouteTest extends TestCase
{
    /** @test */
    public function routes_can_be_resolved_from_resource()
    {
        $user = factory(DummyUser::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->reloadRoutes();

        $this->assertStringEndsWith('foo', $user->route);
    }

    /** @test */
    public function permalink_with_models_will_get_a_default_route_name()
    {
        factory(DummyUser::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->reloadRoutes();

        $this->assertStringEndsWith('foo', route('permalink.1'));
    }

    /** @test */
    public function permalink_without_model_route_name_is_generated_by_route_and_action_if_present()
    {
        Permalink::create(['slug' => 'foo', 'action' => DummyController::class . '@index']);

        $this->reloadRoutes();

        $this->assertStringEndsWith('foo', route('dummy.index'));
    }

    /** @test */
    public function route_name_will_be_same_as_action_map_if_provided()
    {
        Permalink::actionMap(['foo.index' => DummyController::class . '@index']);
        Permalink::create(['slug' => 'foo', 'action' => 'foo.index']);

        $this->reloadRoutes();

        $this->assertStringEndsWith('foo', route('foo.index'));
    }

    /** @test */
    public function nested_routes_are_correctly_formed()
    {
        $root = Permalink::create(['slug' => 'foo', 'action' => DummyController::class . '@foo']);
        $parent = Permalink::create(['slug' => 'bar', 'parent_id' => $root->id, 'action' => DummyController::class . '@bar']);
        Permalink::create(['slug' => 'baz', 'parent_id' => $parent->id, 'action' => DummyController::class . '@baz']);

        $this->reloadRoutes();

        $this->assertStringEndsWith('foo/bar/baz', route('dummy.baz'));
    }
}