<?php

namespace Devio\Permalink\Contracts;

interface Permalinkable
{
    /**
     * Get the permalink action for the model.
     *
     * @return string
     */
    public function permalinkAction();

    /**
     * Get the options for the sluggable package.
     *
     * @return array
     */
    public function slugSource();
}