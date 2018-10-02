<?php

namespace Devio\Permalink\Tests;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\Dummy\DummyUser;
use Devio\Permalink\Tests\Dummy\DummyUserWithoutPermalinkManager;

class HasPermalinkTest extends TestCase
{
    /** @test */
    public function get_permalink_slug_from_entity()
    {
        $user = factory(DummyUser::class)->create(['name' => 'Israel Ortuño']);

        $this->assertEquals('israel-ortuno', $user->slug);
    }

    /** @test */
    public function get_null_if_no_permalink_attached()
    {
        $user = factory(DummyUserWithoutPermalinkManager::class)->create(['name' => 'Israel Ortuño']);

        $this->assertNull($user->slug);
    }

    /** @test */
    public function get_url_path_from_permalink_parents_and_slug()
    {
        $parent = Permalink::create(['slug' => 'foo', 'parent_for' => DummyUser::class]);
        $user = factory(DummyUser::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->reloadRoutes();

        $this->assertEquals('foo/foo', $user->fullSlug);
    }
}