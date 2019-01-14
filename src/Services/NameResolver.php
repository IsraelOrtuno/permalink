<?php

namespace Devio\Permalink\Services;

use Devio\Permalink\Permalink;

class NameResolver implements \Devio\Permalink\Contracts\NameResolver
{
    public function resolve(Permalink $permalink)
    {
        $entity = $permalink->getRelationValue('entity');

        // If the entity has a fallback method to build a custom name for the
        // permalink, it'll be used. If the action is a string@action, then
        // it'll generate a "class.action.key" string like "user.index.1".
        if ($entity && $name = $entity->permalinkRouteName()) {
            return $name;
        } elseif ($action = $permalink->getActionRootName()) {
            return implode('.', [$action, $entity ? $entity->getKey() : '']);
        }

        // TODO: Maybe in future we can add a method fallback in order to
        // customize the generated name if none other was resolved.
        return 'permalink.' . $permalink->getKey();
    }
}