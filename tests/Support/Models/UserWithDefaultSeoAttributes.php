<?php

namespace Devio\Permalink\Tests\Support\Models;

class UserWithDefaultSeoAttributes extends User
{
    public $table = 'users';

    public function getPermalinkSeoTitleAttribute()
    {
        return 'title';
    }

    public function getPermalinkSeoDescriptionAttribute()
    {
        return 'description';
    }

    public function getPermalinkSeoTwitterTitleAttribute()
    {
        return 'twitter title';
    }

    public function getPermalinkSeoTwitterDescriptionAttribute()
    {
        return 'twitter description';
    }

    public function getPermalinkSeoOpengraphTitleAttribute()
    {
        return 'og title';
    }

    public function getPermalinkSeoOpengraphDescriptionAttribute()
    {
        return 'og description';
    }
}