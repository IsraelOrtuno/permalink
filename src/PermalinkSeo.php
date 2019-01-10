<?php

namespace Devio\Permalink;

use Illuminate\Http\Request;
use Devio\Permalink\Routing\Route;
use Illuminate\Contracts\Container\Container;
use Devio\Permalink\Contracts\RequestHandler as RequestHandlerContract;

class PermalinkSeo implements RequestHandlerContract
{
    const builders = ['base', 'meta', 'twitter', 'opengraph'];

    /**
     * Current request.
     *
     * @var Request
     */
    protected $request;

    /**
     * Static permalink collection.
     *
     * @var array
     */
    protected $staticPermalinks = [];

    /**
     * The container instance.
     *
     * @var Container
     */
    protected $container;

    /**
     * Manager constructor.
     *
     * @param $request
     * @param $container
     */
    public function __construct($request, $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function runBuilders()
    {
        if (is_null($permalink = $this->getCurrentPermalink())) {
            return;
        }

        $builders = $this->getBuildersCollection($permalink);

        foreach ($builders as $builder => $data) {
            if ($this->getContainer()->has($binding = 'permalink.' . $builder)) {
                $this->getContainer()->make($binding, [$permalink, $data])->build();
            }
        }
    }

    /**
     * Prepare the builders array from the permalink SEO data.
     *
     * @param $seo
     * @return array
     */
    protected function getBuildersCollection($permalink)
    {
        $seo = array_wrap($permalink->seo);

        if (count($base = array_except($seo, static::builders))) {
            $seo['base'] = $base;
        }

        return collect(static::builders)->mapWithKeys(function ($builder) use ($seo) {
            return [$builder => array_get($seo, $builder)];
        });
    }

    /**
     * Get the current route permalink if any.
     *
     * @return Permalink
     */
    protected function getCurrentPermalink()
    {
        $route = $this->request->route();

        if ($route instanceof Route && ($permalink = $route->permalink()) instanceof Permalink) {
            return $permalink;
        }

        if ($seo = $this->staticPermalinks[$route->getName()] ?? false) {
            return (new Permalink)->fill(compact('seo'));
        }

        return null;
    }

    /**
     * Set the permalink static collection.
     *
     * @param $permalinks
     * @return $this
     */
    public function permalinks($permalinks)
    {
        $this->staticPermalinks = $permalinks;

        return $this;
    }

    /**
     * Add a new permalink to the static collection.
     *
     * @param $route
     * @param array $permalink
     * @return $this
     */
    public function addPermalink($route, $permalink = [])
    {
        $this->permalinks[$route] = $permalink;

        return $this;
    }

    /**
     * Set the request instance.
     *
     * @param $request
     * @return $this
     */
    public function request($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the container instance.
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the container instance.
     *
     * @param $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}