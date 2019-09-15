<?php

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Services\PathBuilder;
use Devio\Permalink\Tests\Support\Models\User;

class PathBuilderTest extends TestCase
{
    /** @test */
    public function it_generates_parent_path()
    {
        Permalink::create([
            'slug'       => '/users',
            'parent_for' => User::class
        ]);

        $slugs = PathBuilder::parentPath(User::class);

        $this->assertEquals(['users'], $slugs);
    }

    /** @test */
    public function it_can_generate_parent_path_with_object_instance()
    {
        Permalink::create([
            'slug'       => '/users',
            'parent_for' => User::class
        ]);

        $slugs = PathBuilder::parentPath(new User);

        $this->assertEquals(['users'], $slugs);
    }

    /** @test */
    public function it_nest_paths_recursively()
    {
        Permalink::create([
            'slug' => 'account'
        ]);

        Permalink::create([
            'slug'       => '/users',
            'parent_for' => User::class,
            'parent_id'  => 1
        ]);


        $slugs = PathBuilder::parentPath(new User);

        $this->assertEquals(['account', 'users'], $slugs);
    }
}
