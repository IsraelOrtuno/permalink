<?php

namespace Devio\Permalink\Tests\Feature\Permalink;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\Support\Models\Company;
use Devio\Permalink\Tests\Support\Controllers\TestController;
use Devio\Permalink\Tests\Support\Models\UserWithDisabledPermalinkHandling;

class CreatePermalinkTest extends \Devio\Permalink\Tests\TestCase
{
    /** @test */
    public function it_gets_unique_slug()
    {
        $permalink = Permalink::create(['slug' => 'foo', 'action' => TestController::class]);
        $permalink2 = Permalink::create(['slug' => 'foo', 'action' => TestController::class]);

        $this->assertEquals('foo', $permalink->slug);
        $this->assertEquals('foo-1', $permalink2->slug);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo']);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo-1']);
    }

    /** @test */
    public function it_can_create_a_nested_permalink()
    {
        $parent = factory(Company::class)->create(['name' => 'foo']);
        $user = factory(UserWithDisabledPermalinkHandling::class)
            ->create(['name' => 'bar',]);

        $user->createPermalink(['parent_id' => $parent->permalink->id]);

        $this->assertDatabaseHas('permalinks', ['final_path' => 'foo/bar']);
    }

    /** @test */
    public function it_can_create_nested_permalink_from_manager()
    {
        $manager = new \Devio\Permalink\PermalinkManager;
        $parent = factory(Company::class)->create(['name' => 'foo']);
        $user = factory(UserWithDisabledPermalinkHandling::class)
            ->create(['name' => 'bar',]);

        $manager->create($user, ['parent_id' => $parent->permalink->id]);

        $this->assertDatabaseHas('permalinks', ['final_path' => 'foo/bar']);
    }
}
