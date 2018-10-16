<?php

namespace Devio\Permalink\Tests;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\Dummy\DummyUser;
use Devio\Permalink\Tests\Dummy\DummyController;
use Illuminate\Database\Eloquent\Relations\Relation;
use Devio\Permalink\Tests\Dummy\DummyUserWithMutators;
use Devio\Permalink\Tests\Dummy\DummyUserWithoutPermalinkManager;

class PermalinkCreationTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Reset maps
        Relation::morphMap([], false);
        Permalink::actionMap([], false);
    }

    /** @test */
    public function permalink_is_automatically_created_by_default()
    {
        $user = factory(DummyUser::class)->create(['name' => 'Israel Ortuño']);

        $this->assertNotNull($user->permalink);

        $this->assertDatabaseHas('permalinks', ['slug' => 'israel-ortuno']);
    }

    /** @test */
    public function permalink_is_not_created_if_manager_is_disabled()
    {
        $user = factory(DummyUserWithoutPermalinkManager::class)->create(['name' => 'Israel Ortuño']);

        $this->assertNull($user->permalink);
    }

    /** @test */
    public function permalink_slug_is_always_unique()
    {
        factory(DummyUser::class)->times(2)->create(['name' => 'foo']);

        $this->assertDatabaseHas('permalinks', ['slug' => 'foo']);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo-1']);
    }

    /** @test */
    public function permalink_relation_is_autoatically_loaded_when_automatically_created()
    {
        $user = factory(DummyUser::class)->create(['name' => 'Israel Ortuño']);

        $this->assertTrue($user->relationLoaded('permalink'));
    }

    /** @test */
    public function permalinkable_entity_supports_morph_map()
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

    /** @test */
    public function permalink_attributes_can_be_set_when_creating_resource()
    {
        $user = factory(DummyUser::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->assertEquals('foo', $user->permalink->slug);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo']);
    }

    /** @test */
    public function provided_permalink_slug_will_always_be_unique()
    {
        Permalink::create(['slug' => 'foo', 'action' => DummyController::class . '@index']);
        $user = factory(DummyUser::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->assertEquals('foo-1', $user->permalink->slug);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo-1']);
    }

    /** @test */
    public function permalink_is_automatically_nested_if_default_parent_is_set()
    {
        $parent = Permalink::create(['slug' => 'foo', 'parent_for' => DummyUser::class, 'action' => DummyController::class . '@index']);
        $user = factory(DummyUser::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->assertEquals($parent->id, $user->permalink->parent_id);
    }

    /** @test */
    public function permalink_is_nested_with_morphed_model_name()
    {
        Relation::morphMap(['user' => DummyUser::class]);
        $parent = Permalink::create(['slug' => 'foo', 'parent_for' => 'user', 'action' => DummyController::class . '@index']);
        $user = factory(DummyUser::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->assertEquals($parent->id, $user->permalink->parent_id);
    }

    /** @test */
    public function permalink_is_nested_with_morphed_model_name_with_full_class_name()
    {
        Relation::morphMap(['user' => DummyUser::class]);
        $parent = Permalink::create(['slug' => 'foo', 'parent_for' => DummyUser::class, 'action' => DummyController::class . '@index']);
        $user = factory(DummyUser::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->assertEquals($parent->id, $user->permalink->parent_id);
    }

    /** @test */
    public function permalink_creation_accepts_dot_nested_arrays()
    {
        $user = factory(DummyUser::class)->create([
            'permalink' => [
                'seo.meta' => ['title' => 'foo', 'description' => 'bar']
            ]
        ]);

        $this->assertEquals('foo', $user->permalink->seo['meta']['title']);
        $this->assertEquals('bar', $user->permalink->seo['meta']['description']);
    }

    /** @test */
    public function fallback_function_will_populate_seo_attributes()
    {
        $user = factory(DummyUserWithMutators::class)->create();

        $this->assertEquals('foo', $user->permalink->seo['title']);
        $this->assertEquals('bar', $user->permalink->seo['description']);
    }

    /** @test */
    public function fallback_function_will_be_skipped_if_value_is_given()
    {
        $user = factory(DummyUserWithMutators::class)->create([
            'permalink' => [
                'seo' => ['title' => 'custom']
            ]
        ]);

        $this->assertEquals('custom', $user->permalink->seo['title']);
    }
}