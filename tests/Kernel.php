<?php

namespace Devio\Permalink\Tests;

class Kernel extends \Orchestra\Testbench\Http\Kernel
{
    use \Devio\Permalink\Routing\ReplacesRouter;
}