<?php

namespace Devio\Permalink\Http;

use Devio\Permalink\Permalink;

trait ResolvesPermalinkView
{
    /**
     * Resolves the permalink view.
     *
     * @param Permalink $permalink
     * @param mixed ...$params
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \ReflectionException
     */
    public function view(Permalink $permalink, ...$params)
    {
        $data = [];

        foreach ($params as $param) {
            $key = strtolower((new \ReflectionClass($param))->getShortName());
            $data[$key] = $param;
        }

        return view($permalink->rawAction, $data);
    }
}
