<?php

namespace Devio\Permalink\Tests\Unit;

use Illuminate\Support\Arr;
use Devio\Permalink\Tests\TestCase;

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
