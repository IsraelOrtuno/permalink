<?php

namespace Devio\Permalink\Meta\Builder;

class MetaBuilder extends Builder
{
    /**
     * Add the robots meta parameters.
     *
     * @param $content
     */
    protected function setRobots($content)
    {
        $this->helper->meta()->getMiscEntity()->add('robots', $content);
    }

    /**
     * Set the canonical URL.
     *
     * @param $content
     */
    public function setCanonical($content)
    {
        $this->helper->meta()->getMiscEntity()->setUrl($content);
    }

    /**
     * @inheritdoc
     */
    public function disable(): void
    {
        // In this particular case we will assume that the Meta translator can
        // not be disabled as it will disable the page title. To change this
        // behaviour, feel free to replace this class using the container.
    }
}