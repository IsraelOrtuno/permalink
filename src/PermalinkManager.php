<?php

namespace Devio\Permalink;

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
                            ->fill($this->prepareSeoAttributes($entity, $attributes));

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

    protected function prepareSeoAttributes($entity, $attributes)
    {
        $attributes = array_undot($attributes);
        $values = array_dot($this->emptySeoArray());

        foreach ($values as $key => $value) {
            $attribute = studly_case(str_replace('.', ' ', $key));

            if (! array_get($attributes, $key) && $value = $entity->getAttribute($attribute)) {
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
    protected function emptySeoArray()
    {
        $fields = ['title' => null, 'description' => null];

        return [
            'seo' => array_merge($fields, [
                'twitter' => $fields, 'opengraph' => $fields,
            ]),
        ];
    }
}