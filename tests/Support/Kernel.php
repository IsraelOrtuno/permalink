<?php

namespace Devio\Permalink\Tests\Support;

use Devio\Permalink\Routing\ReplacesRouter;

class Kernel extends \Orchestra\Testbench\Http\Kernel
{
    use ReplacesRouter;
}