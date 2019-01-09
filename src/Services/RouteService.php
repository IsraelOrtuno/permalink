<?php

namespace Devio\Permalink\Services;

use Devio\Permalink\Permalink;
use Devio\Permalink\Contracts\Permalinkable;

class RouteService
{
    public function permalink($item)
    {
        if (is_numeric($item)) {
            $item = Permalink::find($item);
        } elseif ($item instanceof Permalinkable) {
            $item = $item->permalink;
        }

        return $item instanceof Peramlink ? url($item->full_path) : '#';
    }
}