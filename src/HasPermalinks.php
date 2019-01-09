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
        return $this->morphOne(Permalink::class, 'entity')->withTrashed();
    }

    /**
     * Create the permalink for the current entity.
     *
     * @param $attributes
     * @return $this
     */
    public function createPermalink($attributes)
    {
        if ($this->permalink) {
            return $this->updatePermalink($attributes);
        }

        $permalink = $this->permalink()->newRelatedInstanceFor($this);

        $permalink->fill($this->preparePermalinkSeoAttributes($attributes))
                  ->save();

        $permalink->setRelation('entity', $this);

//        $permalink = $this->permalink()->create(
        //            $this->preparePermalinkSeoAttributes($attributes)
        //        );

        return $this->setRelation('permalink', $permalink);
    }

    /**
     * Update the permalink for the current entity.
     *
     * @param $attributes
     * @return $this
     */
    public function updatePermalink($attributes)
    {
        if (! $this->permalink) {
            return $this->createPermalink($attributes);
        }

        $this->permalink->update($attributes);

        return $this;
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
                'twitter' => $fields, 'opengraph' => $fields,
            ]),
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
            $this->permalink->final_path : null;
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
     * Get the permalink nested path.
     *
     * @return mixed
     */
    public function getRoutePathAttribute()
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
     * @throws \ReflectionException
     */
    public function permalinkRouteName()
    {
        // You can be creative here. Make sure the route name is unique or it may create route conflicts
        return camel_case((new \ReflectionClass($this))->getShortName()) . '.' . $this->getKey();
    }

    public function handlesPermalink()
    {
        if (property_exists($this, 'handlesPermalink')) {
            return $this->handlesPermalink;
        }

        return true;
    }
}