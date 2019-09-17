<?php

namespace Devio\Permalink\Tests\Feature\HasPermalinks;

use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Support\Models\User;
use Devio\Permalink\Tests\Support\Models\UserWithSoftDeletes;

class DeletingTest extends TestCase
{
    /** @test */
    public function it_deletes_permalink_in_cascade()
    {
        $user = factory(User::class)->create();
        $user->delete();
        $this->assertFalse($user->permalink->exists);
    }

    /** @test */
    public function it_softdeletes_permalink_in_cascade()
    {
        $user = factory(UserWithSoftDeletes::class)->create();
        $user->delete();
        $this->assertTrue($user->permalink->trashed());
    }

    /** @test */
    public function it_forces_permalink_deletion_if_entity_forces_deletion()
    {
        $user = factory(UserWithSoftDeletes::class)->create();
        $user->forceDelete();
        $this->assertFalse($user->permalink->exists);
    }

    /** @test */
    public function it_restores_permalink()
    {
        $user = factory(UserWithSoftDeletes::class)->create();
        $user->delete();
        $user->restore();
        $this->assertFalse($user->permalink->trashed());
    }

    /** @test */
    public function it_wont_delete_permalink_if_handling_is_disabled()
    {
        $user = factory(User::class)->create();
        $user->disablePermalinkHandling();
        $user->delete();
        $this->assertTrue($user->hasPermalink());
        $this->assertDatabaseHas('permalinks', ['entity_id' => $user->id]);
    }
}