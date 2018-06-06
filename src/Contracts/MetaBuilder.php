<?php

namespace Devio\Permalink\Contracts;

interface MetaBuilder
{
    /**
     * Translate the current instance from database to the SEO helper.
     *
     * @param array $data
     */
    public function translate($data = []): void;

    /**
     * Defines how to disable the translator.
     *
     * @return void
     */
    protected function disable(): void;
}