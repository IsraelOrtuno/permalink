<?php

namespace Devio\Permalink\Meta\Builder;

class MetaBuilder extends Builder
{
    public function title($content)
    {
        $this->helper->meta()->setTitle($content);
    }

    public function description($content)
    {
        $this->helper->meta()->setDescription($content);
    }

    protected function robots($content)
    {
        $this->helper->meta()->getMiscEntity()->add('robots', $content);
    }

    public function canonical($content)
    {
        $this->helper->meta()->getMiscEntity()->setUrl($content)
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