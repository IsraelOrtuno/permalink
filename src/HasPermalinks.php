<?php

namespace Devio\Permalink;

trait HasPermalinks
{
    /**
     * Permalink attributes.
     *
     * @var null
     */
    protected $permalinkAttributes = null;

//    /**
//     * The permalink parent id.
//     *
//     * @var null
//     */
//    protected $permalinkParent = null;

    /**
     * Booting the trait.
     */
    public static function bootHasPermalinks()
    {
        static::observe(EntityObserver::class);
    }

    /**
     * Set the permalink attributes.
     *
     * @param $value
     */
    public function setPermalinkAttribute($value)
    {
        // This method is supposed to be used to store the permalink data from
        // the request. This data can later be retrieved by the saving event
        // and stored into the permalinks table. This is a kind of bridge.
        $this->permalinkAttributes = $value;
    }

    /**
     * Get the permalink attributes if any.
     *
     * @return null
     */
    public function getPermalinkAttributes()
    {
        return $this->permalinkAttributes;
    }

    /**
     * Relation to the permalinks table.
     *
     * @return mixed
     */
    public function permalink()
    {
        return $this->morphOne(Permalink::class, 'entity');
    }

    /**
     * Store a permalink for this the current entity.
     *
     * @param array $attributes
     * @return $this
     */
    public function storePermalink($attributes = [])
    {
        // Once we have the attributes we need to set, we will perform a new
        // query in order to find if there is any parent class set for the
        // current permalinkable entity. If so, we'll add it as parent.
        if (! isset($attributes['parent_id'])) {
            $attributes = $this->setPermalinkParentIfAny($attributes);
        }

        // Then we are ready to perform the creation or update action based on
        // the model existence. If the model was recently created, we'll add
        // a new permalink, otherwise, we'll update the existing permalink.
        if ($this->wasRecentlyCreated || ! $this->permalink) {
            $permalink = $this->permalink()->create(
                $this->preparePermalinkSeoAttributes($attributes)
            );

            $this->setRelation('permalink', $permalink);
        } elseif ($this->permalink) {
            $this->permalink->update($attributes);
        }

        return $this;
    }

    /**
     * Set the permalink parent if any.
     *
     * @param array $attributes
     * @return array
     */
    protected function setPermalinkParentIfAny($attributes = [])
    {
        if ($parent = Permalink::parentFor($this)) {
            $attributes['parent_id'] = $parent->getKey();
        }

        return $attributes;
    }

    /**
     * Prepare the seo attributes looking for default values in fallback methods.
     *
     * @param array $attributes
     * @return array
     */
    protected function preparePermalinkSeoAttributes($attributes = [])
    {
        $attributes = array_undot($attributes);
        $values = array_dot($this->getEmptyPermalinkSeoArray());

        foreach ($values as $key => $value) {
            $attribute = studly_case(str_replace('.', ' ', $key));

            if (! array_get($attributes, $key) && $value = $this->getAttribute($attribute)) {
                array_set($attributes, $key, $value);
            }
        }

        return $attributes;
    }

    /**
     * Get a value empty seo column structure.
     *
     * @return array
     */
    protected function getEmptyPermalinkSeoArray()
    {
        $fields = ['title' => null, 'description' => null];

        return [
            'seo' => array_merge($fields, [
                'twitter' => $fields, 'opengraph' => $fields
            ])
        ];
    }

    /**
     * Resolve the full permalink route.
     *
     * @return string
     */
    public function getRouteAttribute()
    {
        return ($this->exists && $this->hasPermalink()) ?
            route($this->permalinkRouteName()) : null;
    }

    /**
     * Get the entity slug.
     *
     * @return null
     */
    public function getRouteSlugAttribute()
    {
        return $this->hasPermalink() ? $this->permalink->slug : null;
    }

    /**
     * @return mixed
     */
    public function getRouteFullSlugAttribute()
    {
        return $this->hasPermalink() ? trim(parse_url($this->route)['path'], '/') : null;
    }

    /**
     * Check if the page has a permalink relation.
     *
     * @return bool
     */
    public function hasPermalink()
    {
        return (bool) ! is_null($this->getRelationValue('permalink'));
    }

    /**
     * Determine the newly created entity should load all routes.
     *
     * @return bool
     */
    public function loadRoutesOnCreate()
    {
        return false;
    }

    /**
     * Get the entity route name (must be unique).
     *
     * @return string
     */
    public function permalinkRouteName()
    {
        // You can be creative here. Make sure the route name is unique or it may create route conflicts
        //    return camel_case((new \ReflectionClass($this))->getShortName()) . '.' . $this->getKey();

        return null;
    }


// TODO: CAN BE DELETED
//    /**
//     * Set the permalink parent.
//     *
//     * @param $value
//     */
//    public function setPermalinkParentAttribute($value)
//    {
//        $this->permalinkParent = $value;
//    }
//
//    /**
//     * Get the permalink parent.
//     *
//     * @return null
//     */
//    public function getPermalinkParentAttribute()
//    {
//        return $this->permalinkParent;
//    }
}