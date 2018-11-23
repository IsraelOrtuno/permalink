<?php

namespace Devio\Permalink\Routing;

use Devio\Permalink\Permalink;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class Query
{
    const ALIAS = 'permalinks';

    protected $request;

    /**
     * Query constructor.
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function match()
    {
        $route = $this->matchAgainstSegments();

        dd($route);
    }

    // USE CASES
    // - Route may be /  <---- CONSIDER THAT
    // - First segments (before reversing) may be part of a global group <---- 💁‍♀️ DEAL WITH IT
    // - 
    protected function matchAgainstSegments()
    {
        $segments = $this->segments();

        try {
            $current = $permalink = $this->getRoot($segments[0] ?? '');

            while ($current && $segment = next($segments)) {
                $nested = $this->getNested($current, $segment);

                $current->setRelation('children', collect([$nested]));

                $current = $nested;
            }
        } catch (ModelNotFoundException $e) {
            return false;
        }

        return $permalink;

        /*$index = 1;

        while ($segment = next($segments)) {
            [$previous, $current] = $this->getAliases($index);

            $query->join(
                "permalinks as {$current}",
                function ($join) use ($segment, $previous, $current) {
                    return $join->on("{$previous}.id", '=', "{$current}.parent_id")
                                ->where("{$current}.slug", $segment);
                }
            );

            $index++;
        }

        return $query->get();*/
    }

    /**
     * @param $slug
     * @return mixed
     */
    protected function getRoot($slug)
    {
        return $this->query()->where('slug', $slug)
                    ->where('parent_id', null)
                    ->firstOrFail();
    }

    /**
     * @param $permalink
     * @param $slug
     * @return \Illuminate\Support\Collection
     */
    protected function getNested($permalink, $slug)
    {
        return $permalink->children(false)
                         ->where('slug', $slug)
                         ->firstOrFail();
    }

    protected function segments()
    {
        return $this->request->segments();
    }

    protected function getAliases($index)
    {
        return [
            $index == 1 ? static::ALIAS : static::ALIAS . ($index - 1),
            static::ALIAS . $index
        ];
    }

    protected function query()
    {
        // return DB::table((new Permalink)->getTable());
        return (new Permalink);
    }
}