<?php

namespace Devio\Permalink\Contracts;

interface SeoBuilder
{
    /**
     * Translate the current instance from database to the SEO helper.
     *
     * @param $builder
     * @param array $data
     */
    public function build($builder, $data = []): void;

    /**
     * Defines how to disable the translator.
     *
     * @return void
     */
    public function disable(): void;
}