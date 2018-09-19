<?php

namespace Devio\Permalink\Tests\Dummy;

class DummyUserWithMutators extends DummyUser
{
    public function getSeoMetaTitleAttribute()
    {
        return 'foo';
    }

    public function getSeoMetaDescriptionAttribute()
    {
        return 'bar';
    }
}