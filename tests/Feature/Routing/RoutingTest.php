<?php

namespace Devio\Permalink\Tests\Feature\Routing;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Devio\Permalink\Tests\Support\Controllers\TestController;

class RoutingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Permalink::create(['slug' => 'foo', 'action' => TestController::class . '@index']);
        Permalink::create(['slug' => 'bar', 'action' => null]);
        Permalink::create(['slug' => 'baz', 'action' => TestController::class . '@other']);

        Permalink::create(['slug' => 'overwritten', 'action' => TestController::class . '@index']);
        Route::get('overwritten', function () {
            return 'overwritten';
        });
    }

    /** @test */
    public function it_can_access_an_existing_permalink_url()
    {
        $this->get('/foo')
             ->assertSuccessful();
    }

    /** @test */
    public function it_can_return_the_content_from_action()
    {
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
        $this->get('/baz')
             ->assertStatus(500);
    }

    /** @test */
    public function it_can_override_permalink_routes()
    {
        $this->get('/overwritten')
             ->assertSee('overwritten');
    }
}