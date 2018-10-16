<?php

namespace Devio\Permalink\Routing;

use Devio\Permalink\Permalink;
use Illuminate\Database\Eloquent\Collection;

class RouteCollection extends Collection
{
    /**
     * Get the route collection tree.
     *
     * @return array|Collection
     */
    public function tree()
    {
        if ($this->isEmpty()) {
            return $this->getDefaultTree();
        }

        $this->load('children', 'permalinkable');

        return $this->all();
    }

    /**
     * Get the default Permalink tree.
     *
     * @return Collection
     */
    protected function getDefaultTree()
    {
        // We will query all the root permalinks and then load all their children
        // relationships recursively. This way we will obtain a tree structured
        // collection in which we can easily iterate from parents to children.
        return Permalink::with('children', 'permalinkable')
                        ->whereNull('parent_id')
                        ->get();
    }
}