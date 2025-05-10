<?php

namespace Kwidoo\Lifecycle\Tests\Unit\Factories;

use Kwidoo\Lifecycle\Core\Pipeline\MiddlewarePipelineBuilder;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Factories\LifecycleMiddlewareFactory;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;

class LifecycleMiddlewareFactoryTest extends TestCase
{
    /** @test */
    public function it_delegates_to_pipeline_builder_with_options()
    {
        // Given
        $options = new LifecycleOptionsData();
        $middlewares = ['middleware1', 'middleware2'];

        $pipelineBuilder = Mockery::mock(MiddlewarePipelineBuilder::class);
        $pipelineBuilder->shouldReceive('build')
            ->once()
            ->with($options)
            ->andReturn($middlewares);

        $factory = new LifecycleMiddlewareFactory($pipelineBuilder);

        // When
        $result = $factory->forOptions($options);

        // Then
        $this->assertSame($middlewares, $result);
    }
}
