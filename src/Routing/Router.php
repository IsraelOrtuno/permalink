<?php

namespace Devio\Permalink\Routing;

use Illuminate\Routing\Router as LaravelRouter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Router extends LaravelRouter
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Routing\Route|void
     */
    public function findRoute($request)
    {
        // First we'll try to find any code defined route for the current request.
        // If no route was found, we can then attempt to find if the URL path
        // matches a existing permalink. If not just rise up the exception.
        try {
            return parent::findRoute($request);
        } catch (NotFoundHttpException $e) {
            return $this->findPermalink($request);
        }
    }

    public function findPermalink($request)
    {
        $route = $this->matchAgainstPermalinks($request);

        throw new NotFoundHttpException;
    }

    protected function matchAgainstPermalinks($request)
    {
        return (new Query($request))->match();
    }
}