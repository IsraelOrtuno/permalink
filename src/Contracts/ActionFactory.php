<?php

namespace Devio\Permalink\Contracts;

use Devio\Permalink\Permalink;

interface ActionFactory
{
    public function resolve(Permalink $permalink);
}
