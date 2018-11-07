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
    protected $permalink;

    /**
     * CustomRoute constructor.
     *
     * @param Permalink $permalink
     */
    public function __construct($methods, $uri, $action, $permalink)
    {
        parent::__construct($methods, $uri, $action);

        $this->permalink = $permalink;

        if ($name = $permalink->name) {
            $this->name($name);
        }
    }

    /**
     * Get the permalink instance.
     *
     * @return Permalink
     */
    public function permalink(): Permalink
    {
        if (is_numeric($this->permalink)) {
            $this->permalink = Permalink::find($this->permalink);
        }

        return $this->permalink;
    }

    /**
     * Just an alias to get the permalink.
     * TODO: DELETE when replaced
     * @deprecated
     * @return Permalink
     */
    public function getPermalink()
    {
        return $this->permalink();
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