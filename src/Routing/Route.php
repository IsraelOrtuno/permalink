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

        if ($name = $this->permalink->name) {
            $this->name($name);
        }

        $this->setDefaults();
    }

    /**
     * Set the permalink entity as default route parameter.
     */
    protected function setDefaults(): void
    {
        if (! $entity = $this->permalink->entity) {
            return;
        }

        $this->defaults(get_class($entity), $entity);
    }

    /**
     * Get the permalink instance.
     *
     * @return Permalink
     */
    public function getPermalink(): Permalink
    {
        return $this->permalink;
    }

    /**
     * Set the permalink instance.
     *
     * @param $permalink
     * @return $this
     */
    public function permalink(Permalink $permalink): self
    {
        $this->permalink = $permalink->setRelations([]);

        return $this;
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
}