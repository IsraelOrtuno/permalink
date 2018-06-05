<?php

namespace Devio\Permalink;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\Debug\Exception\FatalThrowableError;

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

//    public function newQuery()
//    {
//        dd($this);
//    }

//    protected function morphInstanceTo($target, $name, $type, $id, $ownerKey)
//    {
//        if (str_contains($target, '@')) {
////            return null;
//            return new ActionRelation($this->newQuery(), $this);
//        }
//
////        $instance = $this->newRelatedInstance(
////            static::getActualClassNameForMorph($target)
////        );
////
////        return $this->newMorphTo(
////            $instance->newQuery(), $this, $id, $ownerKey ?? $instance->getKeyName(), $type, $name
////        );
//        return parent::morphInstanceTo($target, $name, $type, $id, $ownerKey);
//    }

    /**
     * Create a new model instance for a related model.
     *
     * @param  string $class
     * @return mixed
     */
//    protected function newRelatedInstance($class)
//    {
//        // We need to override the default newRelatedInstance method as when
//        // a browseable type is related to a controller action, the query
//        // will fail. We will just provide an instance of this class.
//        if (str_contains($class, '@')) {
//            return null;
//        }
//
//        return parent::newRelatedInstance($class);
//    }

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
}
