<?php

namespace Devio\Permalink\Services;

use Illuminate\Support\Str;
use Devio\Permalink\Permalink;
use Devio\Permalink\Http\PermalinkController;

class ActionFactory implements \Devio\Permalink\Contracts\ActionFactory
{
    /**
     * Resolve the action for the given permalink.
     *
     * @param Permalink $permalink
     * @return mixed
     */
    public function resolve(Permalink $permalink)
    {
        if ($action = $permalink->rawAction) {
            $action = Permalink::getMappedaction($action) ?? $action;
        } elseif ($entity = $permalink->getRelationValue('entity')) {
            $action = $entity->permalinkAction();
        }

        return tap($this->buildAction($action), function ($action) use ($permalink) {
            abort_unless($action, 404, 'Could not resolve an action for permalink ' . $permalink->id);
        });
    }

    /**
     * Resolve the view or controller for the given action.
     *
     * @param $action
     * @return string
     */
    protected function buildAction($action)
    {
        if (view()->exists($action)) {
            return PermalinkController::class . '@view';
        } elseif (Str::contains($action, '@')) {
            $class = explode('@', $action);
            return method_exists($class[0], $class[1]) ? $action : null;
        }

        return null;
    }
}
