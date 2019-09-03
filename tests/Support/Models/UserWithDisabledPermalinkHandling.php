<?php

namespace Devio\Permalink\Tests\Support\Models;

class UserWithDisabledPermalinkHandling extends User
{
    public $table = 'users';

    public function permalinkHandling()
    {
        return false;
    }
}
