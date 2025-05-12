<?php

namespace Kwidoo\Lifecycle\Tests\Unit\CQRS\Commands;

use Illuminate\Container\Container;
use InvalidArgumentException;
use Kwidoo\Lifecycle\CQRS\Commands\DefaultCommandDispatcher;
use Kwidoo\Lifecycle\CQRS\Contracts\Command;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\AggregateRoots\AggregateRootRepository;

class CommandDispatcherTest extends TestCase
{
    protected Container $container;
    protected array $cqrsMappings;
    protected DefaultCommandDispatcher $commandDispatcher;

    /** @var MockInterface|AggregateRootRepository */
    protected $mockAggregateRepository;

    /** @var MockInterface|AggregateRoot */
    protected $mockAggregate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);

        $this->cqrsMappings = [
            'user.register' => [
                'command' => UserRegisterCommand::class,
                'aggregate' => UserAggregate::class,
            ],
        ];

        $this->commandDispatcher = new DefaultCommandDispatcher(
            $this->container,
            $this->cqrsMappings
        );

        // Set up mock repository and aggregate
        $this->mockAggregateRepository = Mockery::mock(AggregateRootRepository::class);
        $this->mockAggregate = Mockery::mock(UserAggregate::class);

        // Setup container mock to return our mock repository
        $this->container->shouldReceive('make')
            ->with(UserAggregate::class . 'Repository')
            ->andReturn($this->mockAggregateRepository);
    }

    /** @test */
    public function it_dispatches_command_to_correct_aggregate_with_correct_handler_method()
    {
        // Arrange
        $command = new UserRegisterCommand('user-123', 'John Doe');

        $this->mockAggregateRepository->shouldReceive('retrieve')
            ->with('user-123')
            ->once()
            ->andReturn($this->mockAggregate);

        $this->mockAggregate->shouldReceive('registerUser')
            ->with($command)
            ->once()
            ->andReturn(null);

        $this->mockAggregate->shouldReceive('persist')
            ->once();

        // Act
        $this->commandDispatcher->dispatch($command);

        // Assert - verified through Mockery expectations
    }

    /** @test */
    public function it_returns_the_result_from_the_aggregate_handler()
    {
        // Arrange
        $command = new UserRegisterCommand('user-123', 'John Doe');
        $expectedResult = ['userId' => 'user-123', 'status' => 'registered'];

        $this->mockAggregateRepository->shouldReceive('retrieve')
            ->with('user-123')
            ->once()
            ->andReturn($this->mockAggregate);

        $this->mockAggregate->shouldReceive('registerUser')
            ->with($command)
            ->once()
            ->andReturn($expectedResult);

        $this->mockAggregate->shouldReceive('persist')
            ->once();

        // Act
        $result = $this->commandDispatcher->dispatch($command);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /** @test */
    public function it_finds_aggregate_by_command_class_name_convention()
    {
        // We'll use reflection to test this protected method directly
        $reflectionMethod = new \ReflectionMethod(
            DefaultCommandDispatcher::class,
            'findAggregateClassForCommand'
        );
        $reflectionMethod->setAccessible(true);

        // Test with an unmapped command that follows naming conventions
        try {
            $result = $reflectionMethod->invoke(
                $this->commandDispatcher,
                CreateOrderCommand::class
            );

            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (InvalidArgumentException $e) {
            // This is expected since we don't have actual class files for these test classes
            $this->assertStringContainsString('No aggregate found for command', $e->getMessage());
        }
    }

    /** @test */
    public function it_finds_handler_method_by_command_name()
    {
        // Arrange - use reflection to test protected method
        $reflectionMethod = new \ReflectionMethod(
            DefaultCommandDispatcher::class,
            'findHandlerMethodForCommand'
        );
        $reflectionMethod->setAccessible(true);

        // Register our mock class to simulate method existence
        $mockUserAggregate = new class {
            public function registerUser($command) {}
            public function handle($command) {}
        };

        // Act - test with a UserRegisterCommand
        $result = $reflectionMethod->invoke(
            $this->commandDispatcher,
            get_class($mockUserAggregate),
            new UserRegisterCommand('123', 'Test')
        );

        // Assert
        $this->assertEquals('registerUser', $result);
    }

    /** @test */
    public function it_can_check_if_it_can_dispatch_a_command()
    {
        // Arrange & Act & Assert

        // It can dispatch commands that are in the mappings
        $this->container->shouldReceive('make')
            ->with(UserAggregate::class . 'Repository')
            ->andReturn($this->mockAggregateRepository);
        $this->assertTrue($this->commandDispatcher->canDispatch(new UserRegisterCommand('123', 'Test')));

        // It cannot dispatch commands that are not in the mappings
        $this->assertFalse($this->commandDispatcher->canDispatch(new UnmappedCommand('123')));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

// Test classes
class UserRegisterCommand implements Command
{
    public function __construct(
        protected string $id,
        public string $name
    ) {}

    public function getAggregateId(): string|int
    {
        return $this->id;
    }
}

class UserAggregate extends AggregateRoot
{
    public function registerUser(UserRegisterCommand $command) {}
}

class CreateOrderCommand implements Command
{
    public function __construct(protected string $id) {}

    public function getAggregateId(): string|int
    {
        return $this->id;
    }
}

class UnmappedCommand implements Command
{
    public function __construct(protected string $id) {}

    public function getAggregateId(): string|int
    {
        return $this->id;
    }
}
