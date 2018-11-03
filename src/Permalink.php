<?php

namespace Devio\Permalink;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Cviebrock\EloquentSluggable\Services\SlugService;

class Permalink extends Model
{
    use Sluggable;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    public $fillable = ['parent_id', 'parent_for', 'slug', 'action', 'seo'];

    /**
     * Casting attributes.
     *
     * @var array
     */
    protected $casts = [
        'seo' => 'json'
    ];

    /**
     * Array to map action class paths to their alias names in database.
     *
     * @var
     */
    public static $actionMap = [];

    /**
     * Determine if routes should be reloaded when creating a new permalink.
     *
     * @var bool
     */
    public static $loadRoutesOnCreate = false;

    /**
     * Parents cache.
     *
     * @var array
     */
    public static $parents = [];

    /**
     * Booting the model.
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function (Permalink $model) {
            // If the user has provided an slug manually, we have to make sure
            // that that slug is unique. If it is not, the SlugService class
            // will append an incremental suffix to ensure its uniqueness.
            if ($model->isDirty('slug') && ! empty($model->slug)) {
                $model->slug = SlugService::createSlug($model, 'slug', $model->slug, []);
            }
        });

        static::created(function (Permalink $model) {
            if (($model->permalinkable && $model->permalinkable->permalinkLoadRoutesOnCreate) || static::$loadRoutesOnCreate) {
                app('router')->loadPermalinks();
            }
        });
    }

    /**
     * Polymorphic relationship to any entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function permalinkable()
    {
        return $this->morphTo();
    }

    /**
     * Relationship to the parent permalink.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo+
     */
    public function parent()
    {
        return $this->belongsTo(static::class)->with('parent');
    }

    /**
     * Relationship to the permalink children.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id')
                    ->with('children', 'permalinkable');
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        if (! $permalinkable = $this->permalinkable) {
            return [];
        }

        $source = (array) $permalinkable->slugSource();

        // We will look for slug source at the permalinkable entity. That method
        // should return an array with a 'source' key in it. This way the user
        // will be able to provide more parameters to the sluggable options.
        return [
            'slug' => array_key_exists('source', $source) ? $source : compact('source')
        ];
    }

    /**
     * Unique slug constraints.
     *
     * @param Builder $query
     * @param Model $model
     * @param $attribute
     * @param $config
     * @param $slug
     * @return Builder+
     */
    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model, $attribute, $config, $slug)
    {
        return $query->where('parent_id', $model->parent_id);
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

        return static::$parents[$model] = static::$parents[$model] ?? static::where('parent_for', $model)->first();
    }

    /**
     * Get the parent route path.
     *
     * @param $model
     * @return array
     */
    public static function parentPath($model)
    {
        if (! is_object($model)) {
            $model = new $model;
        }

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

        $callable($model->permalink ?: static::parentFor($model)->with('parent')->first());

        return $slugs;
    }

    public function getMethodAttribute()
    {
        // Only GET routes for now, we will support others!

        return ['GET', 'HEAD'];
    }

    /**
     * Replace the action by an alias if any.
     *
     * @param $value
     */
    public function setActionAttribute($value)
    {
        $this->attributes['action'] = array_search($value, static::actionMap()) ?: $value;
    }

    /**
     * Convert an alias to a full action path if any.
     *
     * @return null|string
     */
    public function getActionAttribute()
    {
        if (isset($this->attributes['action']) &&
            $action = static::getMappedAction($this->attributes['action']) ?? $this->attributes['action']) {
            return $action;
        }

        if ($relation = $this->getRelationValue('permalinkable')) {
            return $relation->permalinkAction();
        }

        return null;
    }

    /**
     * Get the raw action value.
     *
     * @return mixed
     */
    public function getRawActionAttribute()
    {
        return $this->attributes['action'] ?? null;
    }

    /**
     * Set the parent for from the morph map if exists.
     *
     * @param $value
     */
    public function setParentForAttribute($value)
    {
        if (! Relation::getMorphedModel($value)) {
            $value = array_search($value, Relation::morphMap()) ?: $value;
        }

        $this->attributes['parent_for'] = $value;
    }

    /**
     * Set or get the alias map for aliased actions
     *
     * @param  array|null $map
     * @param  bool $merge
     * @return array
     */
    public static function actionMap(array $map = null, $merge = true)
    {
        if (! is_null($map)) {
            static::$actionMap = $merge && static::$actionMap
                ? $map + static::$actionMap : $map;
        }

        return static::$actionMap;
    }

    /**
     * Set all seo values without NULLs.
     *
     * @param $value
     */
    public function setSeoAttribute($value)
    {
        if (! is_null($value)) {
            $value = json_encode(array_undot(
                array_filter(array_dot($value), function ($item) {
                    return ! is_null($item);
                })
            ));
        }

        $this->attributes['seo'] = $value;
    }

    /**
     * Get the action associated with a custom alias.
     *
     * @param  string $alias
     * @return string|null
     */
    public static function getMappedAction($alias)
    {
        return static::$actionMap[$alias] ?? null;
    }
}
