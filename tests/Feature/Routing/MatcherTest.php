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
//        DB::table('permalinks')->insert([
//            ['slug' => '']
//        ]);
//
//        $query = new Matcher(Request::create('/'));
//
//        dd($query->match());
    }

    protected function getRequest()
    {
        return M::mock(Request::class)->makePartial();
    }
}