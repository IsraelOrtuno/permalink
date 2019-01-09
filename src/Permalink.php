<?php

namespace Devio\Permalink;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Cviebrock\EloquentSluggable\Sluggable;
use Devio\Permalink\Services\ActionService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;

class Permalink extends Model
{
    use Sluggable, SoftDeletes;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    public $fillable = ['parent_id', 'parent_for', 'entity_type', 'entity_id', 'slug', 'action', 'seo'];

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
        static::creating(PermalinkObserver::class . '@creating');
        static::saving(PermalinkObserver::class . '@saving');
        static::updated(PermalinkObserver::class . '@updated');

//        static::created(function (Permalink $model) {
//            if (($model->entity && $model->entity->loadRoutesOnCreate()) || static::$loadRoutesOnCreate) {
//                app('router')->loadPermalinks();
//            }
//        });

        parent::boot();
    }

    /**
     * Polymorphic relationship to any entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity()
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
        return $this->belongsTo(static::class);
    }

    /**
     * Relationship to the permalink children.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        if (! $entity = $this->getRelationValue('entity')) {
            return [];
        }

        $source = (array) $entity->slugSource();

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

    public function isNested()
    {
        return (bool) ! is_null($this->parent_id);
    }

    /**
     * Get the default verbs.
     *
     * @return array
     */
    public function getMethodAttribute()
    {
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
        return (new ActionService)->action($this);
    }

    /**
     * Get the permalink name.
     *
     * @return null|string
     * @throws \ReflectionException
     */
    public function getNameAttribute()
    {
        $entity = $this->getRelationValue('entity');

        // If the entity has a fallback method to build a custom name for the
        // permalink, it'll be used. If the action is a string@action, then
        // it'll generate a "class.action.key" string like "user.index.1".
        if ($entity && $name = $entity->permalinkRouteName()) {
            return $name;
        } elseif ($action = $this->getActionRootName()) {
            return implode('.', [$action, $entity ? $entity->getKey() : '']);
        }

        // TODO: Maybe in future we can add a method fallback in order to
        // customize the generated name if none other was resolved.
        return 'permalink.' . $this->getKey();
    }

    /**
     * Get the action root base name.
     *
     * @return null|string
     * @throws \ReflectionException
     */
    public function getActionRootName()
    {
        if (! str_contains($action = $this->action, '@')) {
            return null;
        }

        [$class, $method] = explode('@', $action);
        $name = str_replace('Controller', '', (new \ReflectionClass($class))->getShortName());

        return strtolower($name . '.' . $method);
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
