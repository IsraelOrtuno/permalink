<?php

namespace Devio\Permalink\Contracts;

use Devio\Permalink\Permalink;
use Illuminate\Database\Eloquent\Model;

interface ActionFactory
{
    public function action(Model $model);

    public function rootName(Permalink $permalink);
}