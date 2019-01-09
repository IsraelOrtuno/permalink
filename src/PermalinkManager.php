<?php

namespace Devio\Permalink;

use Devio\Permalink\Services\NestingService;

class PermalinkManager
{
    /**
     * PermalinkManager constructor.
     */
    public function __construct()
    {
    }

    public function create()
    {

    }

    public function update()
    {

    }

    public function inherit($model)
    {
        (new NestingService)->nest($model);
    }
}