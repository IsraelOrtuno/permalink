<?php

namespace Devio\Permalink\Tests;

use Mockery as m;
use Devio\Permalink\Builders\Builder;
use Devio\Permalink\Builders\BaseBuilder;
use Arcanedev\SeoHelper\Contracts\SeoHelper;
use Devio\Permalink\Builders\OpenGraphBuilder;
use Arcanedev\SeoHelper\Contracts\Entities\OpenGraph;

class BuilderTest extends TestCase
{
    /** @test */
    public function call_own_builder_methods_first()
    {
        $helper = M::mock(SeoHelper::class);
        $builder = (new CustomBaseBuilder($helper))->data(['title' => 'foo']);

        $helper->shouldNotReceive('setTitle');
        $helper->shouldReceive('ok');

        $builder->build();
    }

    /** @test */
    public function builder_pipes_call_to_seo_helper()
    {
        $helper = M::mock(SeoHelper::class);
        $builder = (new BaseBuilder($helper))->data(['title' => 'foo']);

        $helper->shouldReceive('setTitle')->with('foo')->once();

        $builder->build();
    }

    /** @test */
    public function builder_pipes_call_to_specific_seo_helper()
    {
        $helper = M::mock(SeoHelper::class);
        $ogHelper = M::mock(OpenGraph::class);
        $builder = (new OpenGraphBuilder($helper))->data(['locale' => 'foo']);

        $helper->shouldReceive('openGraph')->once()->andReturn($ogHelper);
        $ogHelper->shouldReceive('setLocale')->with('foo')->once();

        $builder->build();
    }
}

class CustomBaseBuilder extends Builder
{
    public function setTitle()
    {
        $this->helper->ok();
    }

    /**
     * @inheritdoc
     */
    public function disable(): void
    {
    }
}