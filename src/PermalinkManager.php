<?php

namespace Devio\Permalink;

use Illuminate\Http\Request;
use Devio\Permalink\Contracts\Manager;
use Illuminate\Contracts\Container\Container;

class PermalinkManager implements Manager
{
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
        if (is_null($permalink = $this->findPermalink())) {
            return;
        }

        $builders = $this->prepareBuildersArray($permalink);

        foreach ($builders as $builder => $data) {
            if ($this->getContainer()->has($binding = 'permalink.' . $builder) && ! is_null($data)) {
                $this->getContainer()->make($binding)->build($builder, $data);
            }
        }
    }

    /**
     * Prepare the builders array from the permalink SEO data.
     *
     * @param $permalink
     * @return array
     */
    protected function prepareBuildersArray($permalink)
    {
        $keys = ['meta', 'opengraph', 'twitter'];
        $builders = array_only($permalink->seo, $keys);

        return array_prepend($builders, array_except($permalink->seo, $keys), 'base');
    }

    /**
     * Find the permalink object.
     *
     * @return mixed
     */
    protected function findPermalink()
    {
        $route = $this->request->route();

        if (($permalink = $route->permalink()) instanceof Permalink) {
            return $permalink;
        }

        return $this->staticPermalinks[$route->getName()] ?? null;
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