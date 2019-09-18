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

        return $this->buildAction($action);
    }

    /**
     * Resolve the view or controller for the given action.
     *
     * @param $action
     * @return string
     */
    protected function buildAction($action)
    {
        return view()->exists($action)
            ? PermalinkController::class . '@view'
            : $action;
    }
}
