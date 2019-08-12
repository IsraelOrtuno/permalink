<?php

namespace Devio\Permalink\Tests\Support\Controllers;

use Devio\Permalink\Permalink;

class TestController
{
    public function index()
    {
        return 'ok';
    }

    public function manual()
    {
        return request()->route()->permalink()->seo['title'];
    }

    public function typehinted(Permalink $permalink)
    {
        return $permalink->slug;
    }
}