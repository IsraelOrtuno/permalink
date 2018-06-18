<?php

namespace Devio\Permalink\Tests\Dummy;

class DummyUserWithFallback extends DummyUser
{
    public function permalinkSeoMetaTitle()
    {
        return 'foo';
    }

    public function permalinkSeoMetaDescription()
    {
        return 'bar';
    }
}