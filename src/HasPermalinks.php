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
     * The permalink parent id.
     *
     * @var null
     */
    protected $permalinkParent = null;

    /**
     * Booting the trait.
     */
    public static function bootHasPermalinks()
    {
        static::observe(PermalinkableObserver::class);
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
        return $this->morphOne(Permalink::class, 'permalinkable');
    }

    /**
     * Store a permalink for this the current entity.
     *
     * @param array $attributes
     * @return $this
     */
    public function storePermalink($attributes = [])
    {
        $attributes = $this->preparePermalinkAttributes($attributes);

        // Once we have the attributes we need to set, we will perform a new
        // query in order to find if there is any parent class set for the
        // current permalinkable entity. If so, we'll add it as parent.
        if ($parent = Permalink::parentFor($this)->first()) {
            $attributes['parent_id'] = $parent->getKey();
        }

        // Then we are ready to perform the creation or update action based on
        // the model existence. If the model was recently created, we'll add
        // a new permalink, otherwise, we'll update the existing permalink.
        if ($this->wasRecentlyCreated || ! $this->permalink) {
            $this->permalink()->create($attributes);
        } elseif ($this->permalink) {
            $this->permalink->update($attributes);
        }

        return $this;
    }

    /**
     * Prepare the seo attributes looking for default values in fallback methods.
     *
     * @param array $attributes
     * @return array
     */
    protected function preparePermalinkAttributes($attributes = [])
    {
        $attributes = array_undot($attributes);

        $checks = ['seo.meta.title', 'seo.meta.description'];

        foreach ($checks as $check) {
            $method = 'permalink' . studly_case(str_replace('.', ' ', $check));

            if (! array_has($attributes, $check) && method_exists($this, $method)) {
                array_set($attributes, $check, call_user_func([$this, $method]));
            }
        }

        return $attributes;
    }

    /**
     * Set the permalink parent.
     *
     * @param $value
     */
    public function setPermalinkParentAttribute($value)
    {
        $this->permalinkParent = $value;
    }

    /**
     * Get the permalink parent.
     *
     * @return null
     */
    public function getPermalinkParentAttribute()
    {
        return $this->permalinkParent;
    }

    /**
     * Resolve the full permalink route.
     *
     * @return string
     */
    public function getRouteAttribute()
    {
        return ($this->exists && $this->hasPermalink()) ?
            route($this->permalinkRouteName() . '.' . $this->permalink->getKey()) : '#';
    }

    /**
     * Get the entity route name prefix.
     *
     * @return string
     */
    public function permalinkRouteName()
    {
        return 'permalink';
    }

    /**
     * Check if the page has a permalink relation.
     *
     * @return bool
     */
    public function hasPermalink()
    {
        return (bool) ! is_null($this->permalink);
    }
}