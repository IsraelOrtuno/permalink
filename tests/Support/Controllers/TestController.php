<?php

namespace Devio\Permalink\Tests\Support\Controllers;

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
}