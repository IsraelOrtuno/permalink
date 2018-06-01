<?php

namespace Devio\Permalink\Routing;

use UnexpectedValueException;
use Devio\Permalink\Permalink;
use Illuminate\Database\Eloquent\Model;
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
        if (is_null($permalink->browseable_id) && str_contains($permalink->browseable_type, '@')) {
            return $permalink->browseable_type;
        } elseif (($browseable = $permalink->browseable) instanceof Model) {
            // Todo: we could use class_uses_recursive here to check if the model implements the trait
            return $browseable->getPermalinkAction();
        }

        throw new UnexpectedValueException("Invalid route action: [{$browseable}]");
    }
}