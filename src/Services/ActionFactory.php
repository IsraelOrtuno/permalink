<?php

namespace Devio\Permalink\Services;

use ReflectionClass;
use Illuminate\Support\Str;
use Devio\Permalink\Permalink;
use Illuminate\Database\Eloquent\Model;

class ActionFactory implements \Devio\Permalink\Contracts\ActionFactory
{
    public function action(Model $model)
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

    public function rootName(Permalink $permalink)
    {
        if (! Str::contains($action = $permalink->action, '@')) {
            return null;
        }

        [$class, $method] = explode('@', $action);
        $name = str_replace('Controller', '', (new ReflectionClass($class))->getShortName());

        return strtolower($name . '.' . $method);
    }
}
