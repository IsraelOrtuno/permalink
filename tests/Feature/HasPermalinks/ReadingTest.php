<?php

namespace Devio\Permalink\Tests\Feature\HasPermalinks;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Support\Models\User;

class ReadingTest extends TestCase
{
    /** @test */
    public function it_returns_the_permalink_slug()
    {
        $user = factory(User::class)->create(['name' => 'foo']);

        $this->assertEquals('foo', $user->routeSlug);
    }

    /** @test */
    public function it_returns_null_for_not_found_permalink_slug()
    {
        $user = factory(User::class)->make(['name' => 'foo']);
        $user->disablePermalinkHandling(); //  = false;
        $user->save();

        $this->assertNull($user->routeSlug);
    }

    /** @test */
    public function it_can_get_path_from_permalink()
    {
        Permalink::create(['slug' => 'foo', 'parent_for' => User::class]);
        $user = factory(User::class)->create(['name' => 'foo']);

        $this->assertEquals('foo/foo', $user->routePath);
    }
}