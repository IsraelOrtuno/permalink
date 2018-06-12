<?php

namespace Devio\Permalink\Tests;

use Devio\Permalink\Middleware\BuildSeo;
use Devio\Permalink\Permalink;
use Illuminate\Database\Eloquent\Relations\Relation;

class PermalinkTest extends TestCase
{

    /** @test */
    public function permalink_is_automatically_created_by_default()
    {
        $user = factory(DummyUser::class)->create(['name' => 'Israel Ortuño']);

        $this->assertNotNull($user->permalink);

        $this->assertDatabaseHas('permalinks', ['slug' => 'israel-ortuno']);
    }

    /** @test */
    public function permalink_slug_is_always_unique()
    {
        factory(DummyUser::class)->times(2)->create(['name' => 'foo']);

        $this->assertDatabaseHas('permalinks', ['slug' => 'foo']);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo-1']);
    }

    /** @test */
    public function entity_permalinkable_supports_morph_map()
    {
        Relation::morphMap(['user' => DummyUser::class]);
        factory(DummyUser::class)->create();

        $this->assertDatabaseHas('permalinks', ['permalinkable_type' => 'user']);
    }

    /** @test */
    public function permalink_actions_are_mapped_before_saving()
    {
        Permalink::actionMap(['user.index' => 'UserController@index']);
        Permalink::create(['slug' => 'foo', 'action' => 'UserController@index']);

        $this->assertDatabaseHas('permalinks', ['action' => 'user.index']);
    }

    /** @test */
    public function permalink_actions_are_mapped_when_reading()
    {
        Permalink::actionMap(['user.index' => 'UserController@index']);
        $permalink = Permalink::create(['slug' => 'foo', 'action' => 'UserController@index']);

        $this->assertEquals('UserController@index', $permalink->action);
        $this->assertEquals('user.index', $permalink->rawAction);
    }

    /** @test */
    public function permalink_with_model_action_is_provided_by_callback_method()
    {
        $user = factory(DummyUser::class)->create();

        $this->assertEquals('UserController@index', $user->permalink->action);
    }

    /** @test */
    public function permalink_default_action_can_be_overwritten()
    {
        $user = factory(DummyUser::class)->create(['permalink' => ['action' => 'UserController@foo']]);

        $this->assertEquals('UserController@foo', $user->permalink->action);
    }
}