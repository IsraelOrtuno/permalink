<?php

namespace Devio\Permalink\Tests;

use Devio\Permalink\Permalink;
use Devio\Permalink\Routing\ActionResolver;

class ActionResolver extends TestCase
{
    /** @test */
    public function resolve_the_default_row_action()
    {
        $resolver = new ActionResolver;

        $this->assertEquals('controller@method', $resolver->resolve($this->createPermalink()));
    }

    /** @test */
    public function fail_if_no_action_can_be_resolved()
    {
        $resolver = new ActionResolver;

        $this->expectException(\UnexpectedValueException::class);

        $resolver->resolve($this->createPermalink(['action' => null]));
    }

    protected function createPermalink($attributes = [])
    {
        return (new Permalink)->forceFill(array_merge(['id' => 1, 'action' => 'controller@method'], $attributes));
    }
}