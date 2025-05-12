<?php

namespace Kwidoo\Lifecycle\Tests\Unit\CQRS\Commands;

use Illuminate\Container\Container;
use Kwidoo\Lifecycle\CQRS\Commands\CommandFactory;
use Kwidoo\Lifecycle\CQRS\Contracts\Command;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Tests\TestCase;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;

class CommandFactoryTest extends TestCase
{
    protected Container $container;
    protected array $cqrsMappings;
    protected CommandFactory $commandFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Container::getInstance();

        // Setup test mapping
        $this->cqrsMappings = [
            'user.register' => [
                'command' => TestRegisterUserCommand::class,
                'aggregate' => 'UserAggregate',
                'uuid_resolver' => fn($context) => $context['id'] ?? 'new-uuid',
            ],
            'user.update' => [
                'command' => TestUpdateUserCommand::class,
                'aggregate' => 'UserAggregate',
            ],
            'order.create' => [
                'command' => TestCreateOrderCommand::class,
                'aggregate' => 'OrderAggregate',
                'command_factory' => [TestOrderCommandFactory::class, 'createOrderCommand'],
            ],
        ];

        $this->commandFactory = new CommandFactory(
            $this->container,
            $this->cqrsMappings
        );

        // Bind test classes to container
        $this->container->singleton(TestOrderCommandFactory::class, function () {
            return new TestOrderCommandFactory();
        });
    }

    /** @test */
    public function it_creates_command_from_lifecycle_context_data()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'register',
            'user',
            ['id' => 'test-user-id', 'name' => 'John Doe', 'email' => 'john@example.com']
        );

        // Act
        $command = $this->commandFactory->createFromLifecycleData($contextData);

        // Assert
        $this->assertInstanceOf(TestRegisterUserCommand::class, $command);
        $this->assertEquals('test-user-id', $command->getAggregateId());
        $this->assertEquals('John Doe', $command->name);
        $this->assertEquals('john@example.com', $command->email);
    }

    /** @test */
    public function it_creates_command_using_uuid_resolver()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'register',
            'user',
            ['name' => 'John Doe', 'email' => 'john@example.com'] // No ID provided
        );

        // Act
        $command = $this->commandFactory->createFromLifecycleData($contextData);

        // Assert
        $this->assertInstanceOf(TestRegisterUserCommand::class, $command);
        $this->assertEquals('new-uuid', $command->getAggregateId()); // Should use the default from resolver
    }

    /** @test */
    public function it_uses_custom_command_factory_when_specified()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'create',
            'order',
            ['id' => 'order-123', 'items' => ['product1', 'product2']]
        );

        // Act
        $command = $this->commandFactory->createFromLifecycleData($contextData);

        // Assert
        $this->assertInstanceOf(TestCreateOrderCommand::class, $command);
        $this->assertEquals('custom-order-factory-used', $command->getAggregateId());
        $this->assertEquals(['product1', 'product2'], $command->items);
    }

    /** @test */
    public function it_throws_exception_when_no_mapping_exists()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'delete', // No mapping for delete action
            'user',
            ['id' => 'test-user-id']
        );

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("No CQRS mapping found for user.delete");
        $this->commandFactory->createFromLifecycleData($contextData);
    }

    /** @test */
    public function it_throws_exception_when_required_parameter_is_missing()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'update',
            'user',
            ['id' => 'test-user-id'] // Missing required 'changes' parameter
        );

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required parameter changes");
        $this->commandFactory->createFromLifecycleData($contextData);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

// Test command classes
class TestRegisterUserCommand implements Command
{
    private string|int $aggregateId;

    public function __construct(
        string|int $aggregateId,
        public string $name,
        public string $email
    ) {
        $this->aggregateId = $aggregateId;
    }

    public function getAggregateId(): string|int
    {
        return $this->aggregateId;
    }
}

class TestUpdateUserCommand implements Command
{
    private string|int $aggregateId;

    public function __construct(
        string|int $aggregateId,
        public array $changes
    ) {
        $this->aggregateId = $aggregateId;
    }

    public function getAggregateId(): string|int
    {
        return $this->aggregateId;
    }
}

class TestCreateOrderCommand implements Command
{
    private string|int $aggregateId;

    public function __construct(
        string|int $aggregateId,
        public array $items
    ) {
        $this->aggregateId = $aggregateId;
    }

    public function getAggregateId(): string|int
    {
        return $this->aggregateId;
    }
}

class TestOrderCommandFactory
{
    public function createOrderCommand($lifecycleData)
    {
        return new TestCreateOrderCommand(
            'custom-order-factory-used',
            $lifecycleData->context['items'] ?? []
        );
    }
}
