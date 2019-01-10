<?php

namespace Devio\Permalink\Tests\Support\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserWithSoftDeletes extends User
{
    use SoftDeletes;

    public $table = 'users';
}