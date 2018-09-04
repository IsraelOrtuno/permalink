<?php

namespace Devio\Permalink\Builders;

use Devio\Permalink\Contracts\SeoBuilder;
use Arcanedev\SeoHelper\Contracts\SeoHelper;

abstract class Builder implements SeoBuilder
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
     * @param $builder
     * @param array $data
     */
    public function build($builder, $data = []): void
    {
        // If the data is false, will mean that the object we are suppose to build
        // has a value of "false" and will be therefore disabled. This way we is
        // possible to control whether we want Twitter or OpenGraph meta tags.
        if ($data === false) {
            $this->disable();
            return;
        }

        foreach ($data as $key => $content) {
            // Then we will check if there is a method with that name in this
            // class. If so, we'll use it as it may contain any extra logic
            // like compiling the content or doing some transformations.
            if ($method = $this->methodExists($this, $key)) {
                call_user_func_array([$this, $method], compact('content'));
            }

            // If the key matches a method in the SEO helper we will just pass
            // the content as parameter. This gives a lot of flexibility as
            // it allows to manage the package directly from database.
            elseif (method_exists($this->helper, $builder)
                && $method = $this->methodExists($target = $this->helper->$builder(), $key)) {
                call_user_func_array([$target, $method], compact('content'));
            }
        }
    }

    /**
     * Check if the method exists into the given object.
     *
     * @param $object
     * @param $name
     * @return bool|mixed
     */
    protected function methodExists($object, $name)
    {
        $name = studly_case($name);
        $methods = collect(["set{$name}", "add{$name}"]);

        foreach ($methods as $method) {
            if (method_exists($object, $method)) {
                return $method;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    abstract public function disable(): void;
}