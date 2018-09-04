<?php

namespace Devio\Permalink\Builders;

class MetaBuilder extends Builder
{
    /**
     * Add the robots meta parameters.
     *
     * @param $content
     */
    protected function setRobots($content)
    {
        $this->helper->meta()->getMiscEntity()->add(
            'robots', implode(' ', (array) $content)
        );
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
        // In this case we won't perform any action for the Meta builder so it
        // can not be disabled as it would disable the page title. To modify
        // this behaviour, feel free to replace the class in the container.
    }
}