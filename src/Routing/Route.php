<?php

namespace Devio\Permalink\Routing;

use Devio\Permalink\Permalink;
use Illuminate\Database\Eloquent\Relations\Relation;

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

        $this->name($this->getPermalinkRouteName());

        $this->setDefaults();
    }

    /**
     * Set the permalinkable entity as default route parameter.
     */
    protected function setDefaults(): void
    {
        if (! $entity = $this->permalink->permalinkable) {
            return;
        }

        $this->defaults(
            Relation::getMorphedModel($entity->permalinkable_type) ?? $entity->permalinkable_type, $entity->permalinkable
        );
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

    public function getPermalinkRouteName(): string
    {
        if ($permalinkable = $this->permalink->permalinkable) {
            return $permalinkable->permalinkRouteName() . '.' . $this->permalink->getKey();
        }

        $action = $this->permalink->rawAction;

        return str_contains($action, '@') ? $this->getRouteNameFromAction($action) : $action;
    }
}