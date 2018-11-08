<?php

namespace Devio\Permalink\Tests\Feature;

use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Dummy\DummyUser;
use Devio\Permalink\Tests\Support\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;

class CreatingTest extends TestCase
{
    /** @test */
    public function it_permalink_is_created_when_resource()
    {
        $user = factory(User::class)->create();

        $this->assertNotNull($user->permalink);
    }

    /** @test */
    public function it_wont_create_permalink_when_disabled()
    {
        $user = factory(User::class)->make();
        $user->handlePermalink = false;
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
    public function it_can_set_permalink_attribtues()
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