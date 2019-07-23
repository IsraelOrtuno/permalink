<?php

namespace Devio\Permalink\Routing;

use Devio\Permalink\Permalink;

class Route extends \Illuminate\Routing\Route
{
    /**
     * The permalink instance.
     *
     * @var Permalink
     */
    protected $permalink = null;

    /**
     * CustomRoute constructor.
     *
     * @param Permalink $permalink
     */
    public function __construct($methods, $uri, $action, $permalink = null)
    {
        parent::__construct($methods, $uri, $action);

        $this->permalink = $permalink;

        if (! is_null($permalink) && $name = $permalink->name) {
            $this->name($name);
        }
    }

    /**
     * Get the permalink instance.
     *
     * @return Permalink|null
     */
    public function permalink()
    {
        if (is_numeric($this->permalink)) {
            $this->permalink = Permalink::find($this->permalink);
        }

        return $this->permalink;
    }

    /**
     * Alias for permalink().
     *
     * @return Permalink
     */
    public function getPermalink()
    {
        return $this->permalink();
    }

    /**
     * Set the permalink instance.
     *
     * @param Permalink $permalink
     */
    public function setPermalink($permalink)
    {
        if (! $permalink instanceof Permalink) {
            $permalink = $this->buildPermalink($permalink);
        }

        $this->permalink = $permalink;
    }

    /**
     * Check if the current route has a permalink instance attached.
     *
     * @return bool
     */
    public function hasPermalink(): bool
    {
        return (bool) $this->permalink;
    }

    public function buildPermalink(array $permalink)
    {
        return new Permalink($permalink);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForSerialization()
    {
        parent::prepareForSerialization();

        // We will replace the permalink instance for its key when serializing
        // so we won't store the entire Permalink object which would result
        // into a large compiled routes file and lack of memory issues.
        $this->permalink = $this->permalink()->getKey();
    }
}