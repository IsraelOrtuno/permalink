<?php

namespace Devio\Permalink\Builders;

class OpenGraphBuilder extends Builder
{
    /**
     * @inheritdoc
     */
    public function disable(): void
    {
        $this->helper->disableOpenGraph();
    }
}