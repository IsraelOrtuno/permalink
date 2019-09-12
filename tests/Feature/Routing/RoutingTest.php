<?php

namespace Devio\Permalink\Tests\Feature\Routing;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Devio\Permalink\Tests\Support\Controllers\TestController;

class RoutingTest extends TestCase
{
    /** @test */
    public function it_automatically_registers_a_new_permalink_as_route()
    {
        Permalink::create(['slug' => 'foo', 'action' => TestController::class . '@index']);

        $this->get('/foo')
             ->assertSuccessful();
    }

    /** @test */
    public function it_can_access_an_existing_permalink_url()
    {
        Permalink::create(['slug' => 'foo', 'action' => TestController::class . '@index']);

        $this->get('/foo')
             ->assertSuccessful();
    }

    /** @test */
    public function it_can_return_the_content_from_action()
    {
        Permalink::create(['slug' => 'foo', 'action' => TestController::class . '@index']);

        $this->get('/foo')
             ->assertSee('ok');
    }

    /** @test */
    public function it_gives_404_if_action_is_null()
    {
        $this->get('/bar')
             ->assertNotFound();
    }

    /** @test */
    public function it_gives_500_if_action_is_not_found()
    {
        Permalink::create(['slug' => 'baz', 'action' => TestController::class . '@other']);

        $this->get('/baz')
             ->assertStatus(500);
    }

    /** @test */
    public function it_can_override_permalink_routes()
    {
        Permalink::create(['slug' => 'overwritten', 'action' => TestController::class . '@index']);
        Route::get('overwritten', function () {
            return 'overwritten';
        });

        $this->get('/overwritten')
             ->assertSee('overwritten');
    }

    /** @test */
    public function it_can_set_a_permalink_when_creating_a_route()
    {
        Route::get('manual', function () {
            return request()->route()->permalink()->seo['title'];
        })->setPermalink([
            'seo' => ['title' => 'foo']
        ]);

        $this->get('/manual')
             ->assertSee('foo');
    }

    /** @test */
    public function it_injects_the_permalink_instance_if_typehinted_as_parameter()
    {
        Permalink::create(['slug' => 'typehinted', 'action' => TestController::class . '@typehinted']);

        $this->get('/typehinted')->assertSee('typehinted');
    }
}
