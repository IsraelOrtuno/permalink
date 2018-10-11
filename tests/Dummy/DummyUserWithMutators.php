<?php

namespace Devio\Permalink\Tests\Dummy;

class DummyUserWithMutators extends DummyUser
{
    public function getSeoTitleAttribute()
    {
        return 'foo';
    }

    public function getSeoDescriptionAttribute()
    {
        return 'bar';
    }
}