<?php

namespace Devio\Permalink\Tests\Feature\Permalink;

use Devio\Permalink\Permalink;
use Devio\Permalink\Tests\Support\Controllers\TestController;

class UpdatePermalinkTest extends \Devio\Permalink\Tests\TestCase
{
    /** @test */
    public function it_gets_unique_slug_if_exists()
    {
        $permalink = Permalink::create(['slug' => 'foo', 'action' => TestController::class]);
        $permalink2 = Permalink::create(['slug' => 'bar', 'action' => TestController::class]);

        $permalink2->update(['slug' => 'foo']);

        $this->assertEquals('foo-1', $permalink2->slug);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo']);
        $this->assertDatabaseHas('permalinks', ['slug' => 'foo-1']);
    }
}