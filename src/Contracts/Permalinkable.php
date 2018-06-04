<?php

namespace Devio\Permalink\Contracts;

interface Permalinkable
{
    /**
     * Get the permalink action for the model.
     *
     * @return mixed
     */
    public function permalinkAction();
}