<?php

namespace Devio\Permalink\Routing;

use UnexpectedValueException;
use Devio\Permalink\Permalink;
use Devio\Permalink\Contracts\Permalinkable;
use Devio\Permalink\Contracts\ActionResolver as ActionResolverInterface;

class ActionResolver implements ActionResolverInterface
{
    /**
     * Resolve the permalink action based on its type.
     *
     * @param Permalink $permalink
     * @return mixed
     */
    public function resolve(Permalink $permalink)
    {
        if (is_null($permalink->permalinkable_id) && str_contains($permalink->permalinkable_type, '@')) {
            return $permalink->permalinkable_type;
        } elseif (($permalinkable = $permalink->permalinkable) instanceof Permalinkable) {
            return $permalinkable->permalinkAction();
        }

        throw new UnexpectedValueException("Invalid route action: [{$permalink->id}]");
    }
}