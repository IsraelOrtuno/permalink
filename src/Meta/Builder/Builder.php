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
        
        foreach ($data as $key => $content) {
            // If the content is a closure, we will just run it and assume that
            // the closure will handle the entire process of communicating to
            // the SEO helper package. This is mostly for testing purposes.
            if (is_callable($content)) {
                $content();
            }

            // Then we will check if there is a method with that name in this
            // class. If so, we'll use it as it may contain any extra logic
            // like compiling the content or doing some transformations.
            elseif (method_exists($this, $method = camel_case($key))) {
                call_user_func_array([$this, $method], compact('content'));
            }

            // If the key matches a method in the SEO helper we will just pass
            // the content as parameter. This gives a lot of flexibility as
            // it allows to manage the package directly from database.
            elseif (method_exists($this->helper, $method)) {
                call_user_func_array([$this->helper, $method], (array) $content);
            }
        }
    }

    /**
     * @inheritdoc
     */
    abstract public function disable(): void;
}