<?php

namespace Kwidoo\Lifecycle\Tests\Unit\Data;

use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Tests\TestCase;

class LifecycleOptionsDataTest extends TestCase
{
    /** @test */
    public function it_has_default_values_when_instantiated()
    {
        // When
        $options = new LifecycleOptionsData();

        // Then
        $this->assertTrue($options->authEnabled);
        $this->assertTrue($options->eventsEnabled);
        $this->assertTrue($options->trxEnabled);
        $this->assertTrue($options->loggingEnabled);
        $this->assertTrue($options->retryEnabled);
        $this->assertTrue($options->cacheEnabled);
        $this->assertTrue($options->rateLimitEnabled);
    }

    /** @test */
    public function it_can_disable_auth()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options->withoutAuth();

        // Then
        $this->assertTrue($options->authEnabled, 'Original options should be unchanged');
        $this->assertFalse($newOptions->authEnabled);
        $this->assertTrue($newOptions->eventsEnabled);
        $this->assertTrue($newOptions->trxEnabled);
    }

    /** @test */
    public function it_can_disable_events()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options->withoutEvents();

        // Then
        $this->assertTrue($options->eventsEnabled, 'Original options should be unchanged');
        $this->assertFalse($newOptions->eventsEnabled);
        $this->assertTrue($newOptions->authEnabled);
        $this->assertTrue($newOptions->trxEnabled);
    }

    /** @test */
    public function it_can_disable_transactions()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options->withoutTrx();

        // Then
        $this->assertTrue($options->trxEnabled, 'Original options should be unchanged');
        $this->assertFalse($newOptions->trxEnabled);
        $this->assertTrue($newOptions->authEnabled);
        $this->assertTrue($newOptions->eventsEnabled);
    }

    /** @test */
    public function it_can_disable_logging()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options->withoutLogging();

        // Then
        $this->assertTrue($options->loggingEnabled, 'Original options should be unchanged');
        $this->assertFalse($newOptions->loggingEnabled);
        $this->assertTrue($newOptions->authEnabled);
    }

    /** @test */
    public function it_can_disable_retry()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options->withoutRetry();

        // Then
        $this->assertTrue($options->retryEnabled, 'Original options should be unchanged');
        $this->assertFalse($newOptions->retryEnabled);
    }

    /** @test */
    public function it_can_disable_cache()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options->withoutCache();

        // Then
        $this->assertTrue($options->cacheEnabled, 'Original options should be unchanged');
        $this->assertFalse($newOptions->cacheEnabled);
    }

    /** @test */
    public function it_can_disable_rate_limit()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options->withoutRateLimit();

        // Then
        $this->assertTrue($options->rateLimitEnabled, 'Original options should be unchanged');
        $this->assertFalse($newOptions->rateLimitEnabled);
    }

    /** @test */
    public function it_can_chain_multiple_option_changes()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options
            ->withoutAuth()
            ->withoutTrx()
            ->withLogging();

        // Then
        $this->assertTrue($options->authEnabled, 'Original options should be unchanged');
        $this->assertTrue($options->trxEnabled, 'Original options should be unchanged');

        $this->assertFalse($newOptions->authEnabled);
        $this->assertFalse($newOptions->trxEnabled);
        $this->assertTrue($newOptions->loggingEnabled);
        $this->assertTrue($newOptions->eventsEnabled);
    }

    /** @test */
    public function it_can_disable_all_options_at_once()
    {
        // When
        $options = new LifecycleOptionsData();
        $newOptions = $options->withoutAll();

        // Then
        $this->assertFalse($newOptions->authEnabled);
        $this->assertFalse($newOptions->eventsEnabled);
        $this->assertFalse($newOptions->trxEnabled);
        $this->assertFalse($newOptions->loggingEnabled);
        $this->assertFalse($newOptions->retryEnabled);
        $this->assertFalse($newOptions->cacheEnabled);
        $this->assertFalse($newOptions->rateLimitEnabled);
    }

    /** @test */
    public function it_configures_api_preset_correctly()
    {
        // When
        $options = new LifecycleOptionsData();
        $apiOptions = $options->forApi();

        // Then
        $this->assertFalse($apiOptions->trxEnabled, 'API should have transactions disabled');
        $this->assertTrue($apiOptions->rateLimitEnabled, 'API should have rate limiting enabled');
        $this->assertTrue($apiOptions->authEnabled, 'API should still have auth enabled');
    }

    /** @test */
    public function it_converts_to_array_correctly()
    {
        // When
        $options = new LifecycleOptionsData();
        $array = $options->toArray();

        // Then
        $this->assertIsArray($array);
        $this->assertArrayHasKey('auth', $array);
        $this->assertArrayHasKey('events', $array);
        $this->assertArrayHasKey('transactions', $array);
        $this->assertArrayHasKey('logging', $array);
        $this->assertArrayHasKey('retry', $array);
        $this->assertArrayHasKey('cache', $array);
        $this->assertArrayHasKey('rateLimit', $array);

        $this->assertTrue($array['auth']);
    }
}
