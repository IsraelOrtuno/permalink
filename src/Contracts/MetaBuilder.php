<?php

namespace Devio\Permalink\Contracts;

interface MetaBuilder
{
    /**
     * Translate the current instance from database to the SEO helper.
     *
     * @param $builder
     * @param array $data
     */
    public function translate($builder, $data = []): void;

    /**
     * Defines how to disable the translator.
     *
     * @return void
     */
    public function disable(): void;
}