<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Features\Log\LogKeyBuilder;
use Kwidoo\Lifecycle\Tests\TestCase;

class LogKeyBuilderTest extends TestCase
{
    private LogKeyBuilder $keyBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keyBuilder = new LogKeyBuilder();
    }

    public function testBuildBeforeKey()
    {
        $result = $this->keyBuilder->buildBeforeKey('create', 'user');
        $this->assertEquals('Lifecycle start: user.create', $result);

        // Test with different inputs
        $result = $this->keyBuilder->buildBeforeKey('update', 'product');
        $this->assertEquals('Lifecycle start: product.update', $result);
    }

    public function testBuildAfterKey()
    {
        $result = $this->keyBuilder->buildAfterKey('create', 'user');
        $this->assertEquals('Lifecycle complete: user.create', $result);

        // Test with different inputs
        $result = $this->keyBuilder->buildAfterKey('delete', 'comment');
        $this->assertEquals('Lifecycle complete: comment.delete', $result);
    }

    public function testBuildErrorKey()
    {
        $result = $this->keyBuilder->buildErrorKey('create', 'user');
        $this->assertEquals('Lifecycle error: user.create', $result);

        // Test with different inputs
        $result = $this->keyBuilder->buildErrorKey('process', 'order');
        $this->assertEquals('Lifecycle error: order.process', $result);
    }

    public function testKeyFormatConsistency()
    {
        // Verify the general pattern: "Lifecycle {phase}: {resource}.{action}"
        $before = $this->keyBuilder->buildBeforeKey('action', 'resource');
        $after = $this->keyBuilder->buildAfterKey('action', 'resource');
        $error = $this->keyBuilder->buildErrorKey('action', 'resource');

        $this->assertEquals('Lifecycle start: resource.action', $before);
        $this->assertEquals('Lifecycle complete: resource.action', $after);
        $this->assertEquals('Lifecycle error: resource.action', $error);
    }
}
