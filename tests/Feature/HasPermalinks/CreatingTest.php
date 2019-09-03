<?php

namespace Devio\Permalink\Tests\Feature\HasPermalinks;

use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Support\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Devio\Permalink\Tests\Support\Models\UserWithDisabledPermalinkHandling;

class CreatingTest extends TestCase
{
    /** @test */
    public function it_creates_permalink_when_resource_is_created()
    {
        $user = factory(User::class)->create();

        $this->assertNotNull($user->permalink);
    }

    /** @test */
    public function it_wont_create_permalink_when_disabled_by_default()
    {
        $user = factory(UserWithDisabledPermalinkHandling::class)->create();
        $user->save();

        $this->assertNull($user->permalink);
    }

    /** @test */
    public function it_wont_create_permalink_when_disabled()
    {
        $user = factory(User::class)->make();
        $user->disablePermalinkHandling();
        $user->save();

        $this->assertNull($user->permalink);
    }

    /** @test */
    public function it_loads_permalink_relation()
    {
        $user = factory(User::class)->create();

        $this->assertTrue($user->relationLoaded('permalink'));
    }

    /** @test */
    public function it_can_set_permalink_attributes()
    {
        $user = factory(User::class)->create(['permalink' => ['slug' => 'foo', 'parent_id' => 1, 'parent_for' => 'user']]);

        $this->assertEquals('foo', $user->permalink->slug);
        $this->assertEquals(1, $user->permalink->parent_id);
        $this->assertEquals('user', $user->permalink->parent_for);
    }

    /** @test */
    public function it_supports_morph_map()
    {
        Relation::morphMap(['user' => User::class]);
        $user = factory(User::class)->create();

        $this->assertEquals('user', $user->permalink->entity_type);
    }
}
