<?php

namespace Kwidoo\Lifecycle\Tests\Unit\CQRS;

use Illuminate\Container\Container;
use InvalidArgumentException;
use Kwidoo\Lifecycle\CQRS\Commands\CommandFactory;
use Kwidoo\Lifecycle\CQRS\Contracts\Command;
use Kwidoo\Lifecycle\CQRS\Contracts\CommandDispatcher;
use Kwidoo\Lifecycle\CQRS\Contracts\ReadModelRepository;
use Kwidoo\Lifecycle\CQRS\CQRSService;
use Kwidoo\Lifecycle\CQRS\Repositories\ReadModelRepositoryFactory;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class CQRSServiceTest extends TestCase
{
    /** @var MockInterface|Container */
    protected $container;

    /** @var MockInterface|CommandDispatcher */
    protected $commandDispatcher;

    /** @var MockInterface|CommandFactory */
    protected $commandFactory;

    /** @var MockInterface|ReadModelRepositoryFactory */
    protected $repositoryFactory;

    /** @var CQRSService */
    protected $cqrsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);
        $this->commandDispatcher = Mockery::mock(CommandDispatcher::class);
        $this->commandFactory = Mockery::mock(CommandFactory::class);
        $this->repositoryFactory = Mockery::mock(ReadModelRepositoryFactory::class);

        $this->cqrsService = new CQRSService(
            $this->container,
            $this->commandDispatcher,
            $this->commandFactory,
            $this->repositoryFactory,
            [
                'cqrs_transactions' => ['wrap_aggregate_persist' => true],
                'cqrs_events' => ['dispatch_lifecycle_events' => true],
            ]
        );
    }

    /** @test */
    public function it_handles_commands_using_lifecycle_context_data()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'register',
            'user',
            ['name' => 'John Doe']
        );

        $command = Mockery::mock(Command::class);
        $expectedResult = ['success' => true, 'userId' => '123'];

        // Setup expectations
        $this->commandFactory->shouldReceive('createFromLifecycleData')
            ->with($contextData)
            ->once()
            ->andReturn($command);

        $this->commandDispatcher->shouldReceive('dispatch')
            ->with($command)
            ->once()
            ->andReturn($expectedResult);

        // Act
        $result = $this->cqrsService->handleCommand($contextData);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /** @test */
    public function it_can_directly_dispatch_a_command()
    {
        // Arrange
        $command = Mockery::mock(Command::class);
        $expectedResult = ['success' => true];

        $this->commandDispatcher->shouldReceive('dispatch')
            ->with($command)
            ->once()
            ->andReturn($expectedResult);

        // Act
        $result = $this->cqrsService->dispatchCommand($command);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /** @test */
    public function it_queries_read_models_by_id_when_action_is_get()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'get',
            'user',
            ['id' => '123']
        );

        $mockRepository = Mockery::mock(ReadModelRepository::class);
        $mockUser = ['id' => '123', 'name' => 'John Doe'];

        $this->repositoryFactory->shouldReceive('resolve')
            ->with('user')
            ->once()
            ->andReturn($mockRepository);

        $mockRepository->shouldReceive('findById')
            ->with('123')
            ->once()
            ->andReturn($mockUser);

        // Act
        $result = $this->cqrsService->query($contextData);

        // Assert
        $this->assertEquals($mockUser, $result);
    }

    /** @test */
    public function it_queries_read_models_by_criteria_when_action_is_list()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'list',
            'user',
            [
                'active' => true,
                'orderBy' => ['name' => 'asc'],
                'limit' => 10,
                'page' => 2
            ]
        );

        $mockRepository = Mockery::mock(ReadModelRepository::class);
        $mockUsers = [
            ['id' => '123', 'name' => 'Alice'],
            ['id' => '456', 'name' => 'Bob']
        ];

        $this->repositoryFactory->shouldReceive('resolve')
            ->with('user')
            ->once()
            ->andReturn($mockRepository);

        $mockRepository->shouldReceive('findByCriteria')
            ->withArgs(function ($criteria, $orderBy, $limit, $offset) {
                return $criteria['active'] === true &&
                    $orderBy === ['name' => 'asc'] &&
                    $limit === 10 &&
                    $offset === 20; // page 2 * limit 10
            })
            ->once()
            ->andReturn($mockUsers);

        // Act
        $result = $this->cqrsService->query($contextData);

        // Assert
        $this->assertEquals($mockUsers, $result);
    }

    /** @test */
    public function it_calls_custom_repository_methods_for_custom_actions()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'findActive',  // Custom action
            'user',
            ['status' => 'premium']
        );

        $mockRepository = Mockery::mock(ReadModelRepository::class);
        $mockUsers = [
            ['id' => '123', 'name' => 'Alice', 'status' => 'premium'],
            ['id' => '456', 'name' => 'Bob', 'status' => 'premium']
        ];

        $this->repositoryFactory->shouldReceive('resolve')
            ->with('user')
            ->once()
            ->andReturn($mockRepository);

        $mockRepository->shouldReceive('findActive')
            ->with($contextData->context)
            ->once()
            ->andReturn($mockUsers);

        // Act
        $result = $this->cqrsService->query($contextData);

        // Assert
        $this->assertEquals($mockUsers, $result);
    }

    /** @test */
    public function it_falls_back_to_find_by_criteria_when_custom_method_not_found()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'someCustomAction',  // Custom action with no matching method
            'user',
            ['status' => 'premium']
        );

        $mockRepository = Mockery::mock(ReadModelRepository::class);
        $mockUsers = [
            ['id' => '123', 'name' => 'Alice', 'status' => 'premium'],
            ['id' => '456', 'name' => 'Bob', 'status' => 'premium']
        ];

        $this->repositoryFactory->shouldReceive('resolve')
            ->with('user')
            ->once()
            ->andReturn($mockRepository);

        $mockRepository->shouldReceive('someCustomAction')
            ->never(); // Method doesn't exist

        $mockRepository->shouldReceive('findByCriteria')
            ->with($contextData->context, [], null, 0)
            ->once()
            ->andReturn($mockUsers);

        // Act
        $result = $this->cqrsService->query($contextData);

        // Assert
        $this->assertEquals($mockUsers, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
