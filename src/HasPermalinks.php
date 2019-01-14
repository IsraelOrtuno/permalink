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
     * Automatic permalink management.
     *
     * @var bool
     */
    protected $permalinkHandling = true;

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
        return app(PermalinkManager::class)->create($this, $attributes);
    }

    /**
     * Update the permalink for the current entity.
     *
     * @param $attributes
     * @return $this
     */
    public function updatePermalink($attributes)
    {
        return app(PermalinkManager::class)->update($this, $attributes);
    }

    /**
     * Resolve the full permalink route.
     *
     * @return string
     */
    public function getRouteAttribute()
    {
        return ($this->exists && $this->hasPermalink()) ?
            url($this->permalink->final_path) : null;
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

    public function permalinkHandling()
    {
        return $this->permalinkHandling;
    }

    public function enablePermalinkHandling()
    {
        $this->permalinkHandling = true;

        return $this;
    }

    public function disablePermalinkHandling()
    {
        $this->permalinkHandling = false;

        return $this;
    }
}