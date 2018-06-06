<?php

namespace Devio\Permalink;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Permalink extends Model
{
    use Sluggable;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    public $fillable = ['slug', 'action'];

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

    public static function boot()
    {
        static::registerModelEvent('slugging', function($model) {
            return (bool) $model->slug;
        });

        parent::boot();
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
        return $this->belongsTo(static::class);
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
        $source = (array) $this->permalinkable->slugSource();

        // We will look for slug source at the permalinkable entity. That method
        // should return an array with a 'source' key in it. This way the user
        // will be able to provide more parameters to the sluggable options.
        return [
            'slug' => array_key_exists('source', $source) ? $source : compact('source')
        ];
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
        return static::getAliasedAction($this->attributes['action']) ?? $this->attributes['action'];
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
     * Get the action associated with a custom alias.
     *
     * @param  string $alias
     * @return string|null
     */
    public static function getAliasedAction($alias)
    {
        return static::$actionMap[$alias] ?? null;
    }
}
