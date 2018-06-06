<?php

namespace Devio\Permalink\Meta\Builder;

class MetaBuilder extends Builder
{
    /**
     * Defines how to disable the translator.
     *
     * @return mixed
     */
    protected function disable(): void
    {
        // In this particular case we will assume that the Meta translator can
        // not be disabled as it will disable the page title, description and
        // some other
    }
}