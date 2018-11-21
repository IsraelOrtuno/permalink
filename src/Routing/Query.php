<?php

namespace Devio\Permalink\Routing;

use Devio\Permalink\Permalink;
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
    }

    protected function matchAgainstSegments()
    {
        $query = $this->query();
        $segments = array_reverse($this->request->segments());

        $query->where('permalinks.slug', array_pop($segments));
        $index = 1;

        while ($segment = array_pop($segments)) {
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

        return $query->get();
    }

    protected function getAliases($index)
    {
        return [
            $index == 1 ? static::ALIAS : (static::ALIAS . ($index - 1)), 
            static::ALIAS . $index
        ];
    }

    protected function query()
    {
        return DB::table((new Permalink)->getTable());
//        return (new Permalink)->newQuery();
    }
}