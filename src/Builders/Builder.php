<?php

namespace Devio\Permalink\Builders;

use Illuminate\Support\Arr;
use Devio\Permalink\Permalink;
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
     * The Permalink instance.
     *
     * @var Permalink
     */
    protected $permalink;

    /**
     * The extracted SEO data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * MetaBuilder constructor.
     *
     * @param SeoHelper $helper
     */
    public function __construct(SeoHelper $helper, Permalink $permalink = null, $data = [])
    {
        $this->helper = $helper;

        $this->permalink($permalink)->data($data);
    }

    /**
     * Tasks before the builder has run.
     */
    protected function before(): void
    {
    }

    /**
     * Tasks after the buidler has run.
     */
    protected function after(): void
    {
    }

    /**
     * Translate the current instance from database to the SEO helper.
     */
    public function build(): void
    {
        // If the data is false, will mean that the object we are suppose to build
        // has a value of "false" and will be therefore disabled. This way we is
        // possible to control whether we want Twitter or OpenGraph meta tags.
        if ($this->data === false) {
            $this->disable();
            return;
        }

        $this->before();

        foreach ($this->data as $key => $content) {
            $this->call($key, $content);
        }

        $this->after();
    }

    protected function call($name, $content)
    {
        // We will make sure we always provide an array as parameter to the
        // builder methods. This way we could pass multiple parameters to
        // functions like setTitle and addWebmaster. Flexibility on top!
        $content = Arr::wrap($content);

        $builder = $this->getBuilderName();

        // Then we will check if there is a method with that name in this
        // class. If so, we'll use it as it may contain any extra logic
        // like compiling the content or doing some transformations.
        if ($method = $this->methodExists($this, $name)) {
            call_user_func_array([$this, $method], $content);
        }

        // If there is a matching method into the base SEO helper, we will
        // pass the data right to it. This is specially useful to avoid
        // specifying a title for every builder (meta, og & twitter).
        elseif ($method = $this->methodExists($this->helper, $name)) {
            call_user_func_array([$this->helper, $method], $content);
        }

        // If the key matches a method in the SEO helper we will just pass
        // the content as parameter. This gives a lot of flexibility as
        // it allows to manage the package directly from database.
        elseif (method_exists($this->helper, $builder)
            && $method = $this->methodExists($target = $this->helper->$builder(), $name)) {
            call_user_func_array([$target, $method], $content);
        }
    }

    protected function getBuilderName()
    {
        $class = (new \ReflectionClass(static::class))->getShortName();

        return lcfirst(str_replace('Builder', '', $class));
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
        $methods = ["set{$name}", "add{$name}"];

        foreach ($methods as $method) {
            if (method_exists($object, $method)) {
                return $method;
            }
        }

        return false;
    }

    /**
     * Set the builder Permalink.
     *
     * @param Permalink $permalink
     * @return $this
     */
    public function permalink(Permalink $permalink = null)
    {
        $this->permalink = $permalink;

        return $this;
    }

    /**
     * Set the builder data.
     *
     * @param array $data
     */
    public function data($data = [])
    {
        $this->data = $data = array_filter(Arr::wrap($data), function ($item) {
            return ! is_null($item);
        });

        return $this;
    }

    /**
     * @inheritdoc
     */
    abstract public function disable(): void;
}