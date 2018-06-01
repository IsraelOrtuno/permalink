<?php

namespace Devio\Permalink\Contracts;

use Devio\Permalink\Permalink;

interface ActionResolver
{
    public function resolve(Permalink $permalink);
}