<?php

namespace Devio\Permalink\Routing;

use UnexpectedValueException;
use Devio\Permalink\Permalink;
use Devio\Permalink\Contracts\Permalinkable;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        // Even if the permalink has a permalinkable relationship, if there is
        // an action, it will override the default entity action. This will
        // provide a lot of flexibility as any route can be overwritten.
        if ($permalink->action) {
            return $permalink->action;
        }

        $permalinkable = Relation::getMorphedModel($permalink->permalinkable_type) ?? $permalink->permalinkable_type;

        // If the permalink has a proper permalinkable relationship, we can then
        // use the permalinkAction from the model to get the route action. It
        // should include an @ too in order to identify controller/action.
        if (is_subclass_of($permalinkable, Permalinkable::class)) {
            return $permalink->getRelation('permalinkable')->permalinkAction();
        }

        // Is trivial to resolve every route action as it will be risky to create
        // routes without them. We will have to notify that something was wrong
        // in the permalink by throwing an exception including the permalink.
        throw new UnexpectedValueException("Invalid route action: [{$permalink->id}]");
    }
}