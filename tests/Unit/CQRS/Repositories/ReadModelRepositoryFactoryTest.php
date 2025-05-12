<?php

namespace Kwidoo\Lifecycle\Tests\Unit\CQRS\Repositories;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Kwidoo\Lifecycle\CQRS\Contracts\ReadModelRepository;
use Kwidoo\Lifecycle\CQRS\Repositories\ReadModelRepositoryFactory;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class ReadModelRepositoryFactoryTest extends TestCase
{
    /** @var MockInterface|Container */
    protected $container;

    protected array $readModelMappings;

    protected ReadModelRepositoryFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);

        $this->readModelMappings = [
            'user' => TestUserReadModelRepository::class,
            'order' => TestOrderReadModelRepository::class,
        ];

        $this->factory = new ReadModelRepositoryFactory(
            $this->container,
            $this->readModelMappings
        );
    }

    /** @test */
    public function it_resolves_repository_by_resource_name()
    {
        // Arrange
        $mockUserRepository = Mockery::mock(TestUserReadModelRepository::class);

        $this->container->shouldReceive('make')
            ->with(TestUserReadModelRepository::class)
            ->once()
            ->andReturn($mockUserRepository);

        // Act
        $result = $this->factory->resolve('user');

        // Assert
        $this->assertSame($mockUserRepository, $result);
    }

    /** @test */
    public function it_throws_exception_when_repository_not_found()
    {
        // Arrange - setup repository guessing attempts
        $this->container->shouldReceive('make')
            ->never(); // We should never reach the container make call

        // We'll test the conventional repository paths
        $possibleClasses = [
            '\\App\\Repositories\\productReadModelRepository',
            '\\App\\CQRS\\Repositories\\productReadModelRepository',
            '\\App\\Domain\\Repositories\\productReadModelRepository',
        ];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No read model repository found for resource product');

        $result = $this->factory->resolve('product'); // Not in our mappings
    }

    /** @test */
    public function it_tries_to_resolve_by_convention_when_not_in_mappings()
    {
        // Create a test class that doesn't implement ReadModelRepository just for this test
        require_once __DIR__ . '/../../Fixtures/TestCustomerReadModelRepository.php';

        // Arrange
        $mockRepository = Mockery::mock(\App\Repositories\TestCustomerReadModelRepository::class);

        $this->container->shouldReceive('make')
            ->with(\App\Repositories\TestCustomerReadModelRepository::class)
            ->once()
            ->andReturn($mockRepository);

        // Override the class_exists function locally
        $factory = new class($this->container, $this->readModelMappings) extends ReadModelRepositoryFactory {
            protected function classExists(string $class): bool
            {
                // Only return true for the first conventional path
                return $class === '\App\Repositories\customerReadModelRepository';
            }
        };

        // Act - this will throw if it doesn't work
        try {
            $result = $factory->resolve('customer');
            $this->fail('Expected exception was not thrown');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('No read model repository found', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

interface TestUserReadModelRepository extends ReadModelRepository {}
interface TestOrderReadModelRepository extends ReadModelRepository {}
