<?php

namespace Devio\Permalink\Tests\Feature;

use Mockery as M;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Devio\Permalink\Tests\TestCase;
use Devio\Permalink\Routing\Matcher;

class MatcherTest extends TestCase
{
    /** @test */
    public function it_matches_the_empty_route()
    {
        DB::table('permalinks')->insert([
            ['slug' => '', 'action' => 'foo']
        ]);

        $permalink = (new Matcher(Request::create('/')))->match();

        $this->assertEquals('', $permalink->slug);
        $this->assertEquals('foo', $permalink->action);
    }

    protected function getRequest()
    {
        return M::mock(Request::class)->makePartial();
    }
}