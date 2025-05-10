<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Features\Event\EventKeyBuilder;
use Kwidoo\Lifecycle\Tests\TestCase;

class EventKeyBuilderTest extends TestCase
{
    private EventKeyBuilder $keyBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keyBuilder = new EventKeyBuilder();
    }

    public function testBuildBeforeKey()
    {
        $result = $this->keyBuilder->buildBeforeKey('create', 'user');
        $this->assertEquals('before.user.create', $result);

        // Test with different inputs
        $result = $this->keyBuilder->buildBeforeKey('update', 'product');
        $this->assertEquals('before.product.update', $result);
    }

    public function testBuildAfterKey()
    {
        $result = $this->keyBuilder->buildAfterKey('create', 'user');
        $this->assertEquals('after.user.create', $result);

        // Test with different inputs
        $result = $this->keyBuilder->buildAfterKey('delete', 'comment');
        $this->assertEquals('after.comment.delete', $result);
    }

    public function testBuildErrorKey()
    {
        $result = $this->keyBuilder->buildErrorKey('create', 'user');
        $this->assertEquals('error.user.create', $result);

        // Test with different inputs
        $result = $this->keyBuilder->buildErrorKey('process', 'order');
        $this->assertEquals('error.order.process', $result);
    }

    public function testKeyFormatConsistency()
    {
        // Verify the general pattern: phase.resource.action
        $before = $this->keyBuilder->buildBeforeKey('action', 'resource');
        $after = $this->keyBuilder->buildAfterKey('action', 'resource');
        $error = $this->keyBuilder->buildErrorKey('action', 'resource');

        $this->assertEquals('before.resource.action', $before);
        $this->assertEquals('after.resource.action', $after);
        $this->assertEquals('error.resource.action', $error);
    }
}
