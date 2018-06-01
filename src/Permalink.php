<?php

namespace Devio\Permalink;

use Illuminate\Database\Eloquent\Model;

class Permalink extends Model
{
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
}
