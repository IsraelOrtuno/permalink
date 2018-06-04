<?php

namespace Devio\Permalink;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Permalink extends Model
{
    use Sluggable;

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
                    ->with('children');
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        $source = $this->permalinkable->slugSource();

        // We will look for slug source at the permalinkable entity. That method
        // should return an array with a 'source' key in it. This way the user
        // will be able to provide more parameters to the sluggable options.
        return [
            'slug' => array_key_exists('source', $source) ? $source : compact('source')
        ];
    }
}
