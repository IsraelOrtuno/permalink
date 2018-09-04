<?php

namespace Devio\Permalink\Builders;

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