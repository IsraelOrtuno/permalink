<?php

namespace Devio\Permalink\Contracts;

interface RequestHandler
{
    /**
     * Run the builders.
     */
    public function runBuilders();
}