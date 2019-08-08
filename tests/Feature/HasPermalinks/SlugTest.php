<?php

namespace Devio\Permalink\Tests\Feature\HasPermalinks;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Support\Models\User;
use Devio\Permalink\Tests\Support\Models\Company;

class SlugTest extends TestCase
{
    /** @test */
    public function it_creates_slug_from_resource()
    {
        $user = factory(User::class)->create(['name' => 'Israel Ortuño']);

        $this->assertEquals('israel-ortuno', $user->permalink->slug);
        $this->assertDatabaseHas('permalinks', ['slug' => 'israel-ortuno']);
    }

    /** @test */
    public function it_creates_unique_slugs()
    {
        $permalink1 = Permalink::create(['slug' => 'foo']);
        $permalink2 = Permalink::create(['slug' => 'foo']);

        $this->assertEquals('foo', $permalink1->slug);
        $this->assertEquals('foo-1', $permalink2->slug);
    }

    /** @test */
    public function it_creates_unique_slugs_from_resource()
    {
        factory(User::class)->times(2)->create(['name' => 'foo']);

        $this->assertDatabaseHas('permalinks', ['slug' => 'foo']);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo-1']);
    }

    /** @test */
    public function it_creates_same_slug_if_parent_is_different()
    {
        $permalink1 = Permalink::create(['slug' => 'foo']);
        $permalink2 = Permalink::create(['slug' => 'foo', 'parent_id' => 1]);

        $this->assertEquals('foo', $permalink1->slug);
        $this->assertEquals('foo', $permalink2->slug);
    }

    /** @test */
    public function it_creates_same_slug_for_different_parents_from_resource()
    {
        Permalink::create(['slug' => 'user', 'parent_for' => User::class]);
        Permalink::create(['slug' => 'company', 'parent_for' => Company::class]);

        $user = factory(User::class)->create(['name' => 'foo']);
        $company = factory(Company::class)->create(['name' => 'foo']);

        $this->assertEquals('foo', $user->permalink->slug);
        $this->assertEquals('foo', $company->permalink->slug);
    }

    /** @test */
    public function it_creates_unique_slug_when_passing_permalink_array()
    {
        Permalink::create(['slug' => 'foo']);
        $user = factory(User::class)->create(['permalink' => ['slug' => 'foo']]);

        $this->assertEquals('foo-1', $user->permalink->slug);
    }

    /** @test */
    public function it_allows_same_slug_per_parent()
    {
        Permalink::create(['slug' => 'foo', 'parent_for' => User::class]);
        $user = factory(User::class)->create(['name' => 'foo']);

        $this->assertEquals('foo', $user->permalink->slug);
    }
    
    /** @test */
    public function it_makes_slug_attribute_mandatory()
    {
        // Slug is mandatory as there's only one '' permalink => homepage
        $user = factory(User::class)->create(['name' => 'foo', 'permalink' => ['slug' => null]]);
        $user2 = factory(User::class)->create(['name' => 'bar', 'permalink' => ['slug' => '']]);

        $this->assertEquals('foo', $user->permalink->slug);
        $this->assertEquals('bar', $user2->permalink->slug);
    }
}