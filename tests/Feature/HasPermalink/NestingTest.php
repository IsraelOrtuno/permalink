<?php

namespace Devio\Permalink\Tests\Feature;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Illuminate\Database\QueryException;
use Devio\Permalink\Tests\Support\Models\User;

class NestingTest extends TestCase
{
    /** @test */
    public function it_nest_permalink_to_existing_parent()
    {
        factory(User::class)->create();
        $parent = Permalink::create(['slug' => 'foo', 'parent_for' => User::class]);
        $child = Permalink::create(['slug' => 'bar', 'entity_type' => User::class, 'entity_id' => 1]);

        $this->assertEquals($parent->getKey(), $child->parent_id);
        $this->assertCount(1, $parent->children);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    /** @test */
    public function it_will_have_unique_parent_for_records()
    {
        $this->expectException(QueryException::class);

        Permalink::create(['slug' => 'foo', 'parent_for' => User::class]);
        Permalink::create(['slug' => 'foo', 'parent_for' => User::class]);
    }
}