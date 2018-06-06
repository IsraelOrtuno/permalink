<?php

namespace Devio\Permalink\Meta\Builder;

class TwitterBuilder extends Builder
{
    /**
     * @inheritdoc
     */
    public function disable(): void
    {
        $this->helper->disableTwitter();
    }
}