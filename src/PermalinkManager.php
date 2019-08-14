<?php

namespace Devio\Permalink;

use Illuminate\Support\Arr;
use Devio\Permalink\Contracts\Permalinkable;

class PermalinkManager
{
    /**
     * Resolve a route URL.
     *
     * @param $item
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function route($item)
    {
        if (is_numeric($item)) {
            $item = Permalink::find($item);
        } elseif ($item instanceof Permalinkable) {
            $item = $item->permalink;
        }

        return $item instanceof Permalink ? url($item->final_path) : '#';
    }

    /**
     * Create a permalink for the given entity.
     *
     * @param $entity
     * @param array $attributes
     * @return mixed
     */
    public function create($entity, $attributes = [])
    {
        $permalink = $entity->permalink()
                            ->newRelatedInstanceFor($entity)
//                            ->setRelation('entity', $entity)
                            ->fill($this->prepareDefaultSeoAttributes($entity, $attributes));

        $permalink->save();

        return $entity->setRelation('permalink', $permalink);
    }

    /**
     * Update an existing permalink.
     *
     * @param $entity
     * @param array $attributes
     * @return mixed
     */
    public function update($entity, $attributes = [])
    {
        if (! $entity->hasPermalink()) {
            return $entity;
        }

        return $entity->permalink->update($attributes);
    }

    /**
     * Prepare the default SEO attributes.
     *
     * @param $entity
     * @param $attributes
     * @return array
     */
    protected function prepareDefaultSeoAttributes($entity, $attributes)
    {
        $attributes = array_undot($attributes);
        $values = Arr::dot($this->getSeoAttributesArray());

        foreach ($values as $key => $value) {
            // We will generate the permalinkSeo* methods in order to populate the
            // premalink default SEO data when creating a new permalink record.
            // They will be overwritten if any seo data has been provided.
            $attribute = 'permalink' . studly_case(str_replace('.', ' ', $key));

            if (! Arr::get($attributes, $key) && $value = $entity->getAttribute($attribute)) {
                Arr::set($attributes, $key, $value);
            }
        }

        return $attributes;
    }

    /**
     * Get a value empty seo column structure.
     *
     * @return array
     */
    protected function getSeoAttributesArray()
    {
        return [
            'seo' => [
                'title'       => null,
                'description' => null,
                'twitter'     => ['title' => null, 'description' => null],
                'opengraph'   => ['title' => null, 'description' => null],
            ],
        ];
    }
}