<?php

namespace Devio\Permalink\Meta\Builder;

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