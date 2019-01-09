<?php

namespace Devio\Permalink\Services;

use Devio\Permalink\Permalink;

class ActionService
{
    public function action($model)
    {
        if ($action = $model->getAttributes()['action'] ?? false) {
            return Permalink::getMappedaction($action) ?? $action;
        }

        $entity = $model->getRelationValue('entity');

        // If the action is mapped or a fallback action has been set to the
        // permalinkable entity, we will assume it exists. Otherwise it's
        // not possible to provide an action to be bound to the router.
        if ($entity && method_exists($entity, 'permalinkAction')) {
            return $entity->permalinkAction();
        }

        return null;
    }
}