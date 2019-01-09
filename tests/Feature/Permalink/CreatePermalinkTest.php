<?php

namespace Devio\Permalink\Tests\Feature\Permalink;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\Support\Controllers\TestController;

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
}