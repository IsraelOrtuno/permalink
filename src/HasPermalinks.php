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
        // Once we have the attributes we need to set, we will perform a new
        // query in order to find if there is any parent class set for the
        // current permalinkable entity. If so, we'll add it as parent.
        if ($parent = Permalink::parentFor($this)) {
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

    public function setPermalinkParentAttribute($value)
    {
        $this->permalinkParent = $value;
    }

    public function getPermalinkParentAttribute()
    {
        return $this->permalinkParent;
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