<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Authorizers\DefaultAuthorizer;
use Kwidoo\Lifecycle\Tests\Data\TestData;
use Kwidoo\Lifecycle\Tests\TestCase;

class DefaultAuthorizerTest extends TestCase
{
    /** @test */
    public function it_calls_authorize_with_data_object()
    {
        // Arrange
        $authorizer = new DefaultAuthorizer();
        $context = new TestData();

        // Act - Should not throw an exception
        $authorizer->authorize('update', $context);

        // Assert - No assertions needed as method is void
        $this->assertTrue(true); // Just to have an assertion
    }

    /** @test */
    public function it_allows_null_context()
    {
        // Arrange
        $authorizer = new DefaultAuthorizer();

        // Act - Should not throw an exception
        $authorizer->authorize('delete', null);

        // Assert - No assertions needed as method is void
        $this->assertTrue(true); // Just to have an assertion
    }
}
