<?php

namespace Devio\Permalink\Contracts;

use Devio\Permalink\Permalink;

interface ActionResolver
{
    /**
     * Resolve the action based on the given permalink.
     *
     * @param Permalink $permalink
     * @return mixed
     */
    public function resolve(Permalink $permalink);
}