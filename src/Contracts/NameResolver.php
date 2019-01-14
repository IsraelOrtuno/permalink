<?php

namespace Devio\Permalink\Contracts;

use Devio\Permalink\Permalink;

interface NameResolver
{
    public function resolve(Permalink $permalink);
}