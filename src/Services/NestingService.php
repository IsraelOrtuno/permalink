<?php

namespace Devio\Permalink\Services;

use Devio\Permalink\Permalink;

class NestingService
{
    protected $cache = [];

    public function nest($model)
    {
        if (! $model->exists) {
            return $this->single($model);
        } elseif ($model->isDirty('slug')) {
            return $this->recursive($model);
        }

        return $model;
    }

    public function single($model)
    {
        $model->final_path = $this->getFullyQualifiedPath($model);

        return $model;
    }

    public function all()
    {
        $permalinks = Permalink::withTrashed()->get();

        $permalinks->each(function ($permalink) {
            $this->single($permalink);
            $permalink->save();
        });
    }

    public function recursive($model)
    {
        $path = $model->isDirty('slug') ?
            $model->getOriginal('final_path') : $model->final_path;

        $nested = Permalink::withTrashed()
                           ->where('final_path', 'LIKE', $path . '/%')
                           ->get();

        $this->single($model);

        $nested->each(function ($permalink) use ($model) {
            $permalink->final_path = $model->final_path . '/' . $permalink->slug;
            $permalink->save();
        });

        return $model;
    }

    /**
     * @param $model
     * @return string
     */
    public function getFullyQualifiedPath($model)
    {
        $path = ($model->isNested() && $model->parent) ? $model->parent->final_path : '';

        return trim($path . '/' . $model->slug, '/');
    }

    /**
     * Find the parent for the given model.
     *
     * @param $model
     * @return mixed
     */
    public static function parentFor($model)
    {
        if (! is_object($model)) {
            $model = new $model;
        }

        $model = $model->getMorphClass();

        return Permalink::with('parent')->where('parent_for', $model)->first();
    }

    /**
     * Get the parent route path.
     *
     * @param $model
     * @return array
     */
    public static function parentPath($model)
    {
        if (! $model instanceof Permalink) {
            $model = $model->permalink;
        }

//        if (! is_object($model)) {
//            $model = new $model;
//        }

        $slugs = [];

        $callable = function ($permalink) use (&$callable, &$slugs) {
            if (is_null($permalink)) {
                return;
            }

            if ($permalink->parent) {
                array_push($slugs, $callable($permalink->parent));
            }

            return $permalink->slug;
        };

        $callable($model ?: static::parentFor($model));

        return $slugs;
    }
}