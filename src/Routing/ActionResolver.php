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
        // If the permalink does not have a polymorphic id we will assume the type
        // will be containing a controller@index path so we will be returning it
        // as the action route. It must include an @ to split controller/method.
        if (is_null($permalink->permalinkable_id) && str_contains($permalink->permalinkable_type, '@')) {
            return $permalink->permalinkable_type;
        }

        // If the permalink has a proper permalinkable relationship, we can then
        // use the permalinkAction from the model to get the route action. It
        // should include an @ too in order to identify controller/action.
        elseif (($permalinkable = $permalink->permalinkable) instanceof Permalinkable) {
            return $permalinkable->permalinkAction();
        }

        // Is trivial to resolve every route action as it will be risky to create
        // routes without them. We will have to notify that something was wrong
        // in the permalink by throwing an exception including the permalink.
        throw new UnexpectedValueException("Invalid route action: [{$permalink->id}]");
    }
}