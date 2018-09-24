<?php

namespace Devio\Permalink\Tests\Dummy;

class DummyUserWithoutPermalinkManager extends DummyUser
{
    public $managePermalinks = false;
}