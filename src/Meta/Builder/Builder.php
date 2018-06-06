<?php

namespace Devio\Permalink\Meta\Builder;

use Devio\Permalink\Contracts\MetaBuilder;
use Arcanedev\SeoHelper\Contracts\SeoHelper;

abstract class Builder implements MetaBuilder
{
    /**
     * The SEO helper instance.
     *
     * @var SeoHelper
     */
    protected $helper;

    /**
     * MetaBuilder constructor.
     *
     * @param SeoHelper $helper
     */
    public function __construct(SeoHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Translate the current instance from database to the SEO helper.
     *
     * @param array $data
     */
    public function translate($data = []): void
    {
        // If the data is false, will mean that the object we are suppose to build
        // has a value of "false" and will be therefore disabled. This way we is
        // possible to control whether we want Twitter or OpenGraph meta tags.
        if ($data === false) {
            $this->disable();
        }

        // Meanwhile, if we get an array with pairs of key and values, we will
        // search for the method matching the key name in the class and this
        // method will pipe the content of the database to the SEO helper.
        foreach ($data as $key => $content) {
            // The content of the array may be a closure. If it is not a closure
            // we'll look for a method matching the key name prefixed by "set"
            if (is_callable($content)) {
                $content();
            } elseif (method_exists($this, $method = 'set' . studly_case($key))) {
                call_user_func_array([$this, $method], compact('content'));
            }
        }
    }

    /**
     * Defines how to disable the translator.
     *
     * @return mixed
     */
    abstract protected function disable(): void;
}