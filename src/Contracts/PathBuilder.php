<?php

namespace Devio\Permalink\Contracts;

interface PathBuilder
{
    public function build($model);

    public function single($model);

    public function recursive($model);

    public function all();
}
