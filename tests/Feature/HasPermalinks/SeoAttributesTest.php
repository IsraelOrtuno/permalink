<?php

namespace Devio\Permalink\Tests\Feature\HasPermalinks;

use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Tests\Support\Models\UserWithDefaultSeoAttributes;

class SeoAttributesTest extends TestCase
{
    /** @test */
    public function it_gets_default_seo_values_if_accessors_are_defined()
    {
        $user = factory(UserWithDefaultSeoAttributes::class)->create();

        $this->assertEquals('title', $user->permalink->seo['title']);
        $this->assertEquals('description', $user->permalink->seo['description']);
        $this->assertEquals('twitter title', $user->permalink->seo['twitter']['title']);
        $this->assertEquals('twitter description', $user->permalink->seo['twitter']['description']);
        $this->assertEquals('og title', $user->permalink->seo['opengraph']['title']);
        $this->assertEquals('og description', $user->permalink->seo['opengraph']['description']);
    }

    /** @test */
    public function it_overwrites_default_seo_values_if_provided()
    {
        $user = factory(UserWithDefaultSeoAttributes::class)->create([
            'permalink' => [
                'seo' => ['title' => 'foo', 'description' => 'bar baz']
            ]
        ]);

        $this->assertEquals('foo', $user->permalink->seo['title']);
        $this->assertEquals('bar baz', $user->permalink->seo['description']);
    }

    /** @test */
    public function it_wont_save_provided_null_values()
    {
        $user = factory(UserWithDefaultSeoAttributes::class)->create([
            'permalink' => [
                'seo' => ['title' => null, 'description' => null]
            ]
        ]);

        $this->assertFalse(isset($user->permalink->seo['title']));
        $this->assertFalse(isset($user->permalink->seo['description']));
    }

    /** @test */
    public function it_populates_seo_attributes_only_when_creating()
    {
        $user = factory(UserWithDefaultSeoAttributes::class)->create([
            'permalink' => [
                'seo' => ['title' => null, 'description' => null]
            ]
        ]);

        $user->update(['name' => 'bar bar']);

        $this->assertFalse(isset($user->permalink->seo['title']));
    }
}