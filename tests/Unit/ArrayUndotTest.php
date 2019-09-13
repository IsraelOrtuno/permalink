<?php

namespace Devio\Permalink\Tests\Unit;

use Devio\Permalink\Tests\TestCase;
use Illuminate\Support\Arr;

class ArrayUndotTest extends TestCase
{
    /** @test */
    public function it_can_undot_an_array()
    {
        $given = [
            'foo.bar' => 1,
            'foo.baz' => 2
        ];

        $result = Arr::undot($given);

        $this->assertEquals(['foo' => ['bar' => 1, 'baz' => 2]], $result);
    }
}
