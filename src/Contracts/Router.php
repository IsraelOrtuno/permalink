<?php

namespace Devio\Permalink\Contracts;

interface Router
{
    /**
     * Load the routes.
     *
     * @return mixed
     */
    public function load();
}