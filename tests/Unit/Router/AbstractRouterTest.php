<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Router;

use MerchantOfComplexity\ServiceBus\Router\AbstractRouter;
use MerchantOfComplexityTest\ServiceBus\TestCase;
use Psr\Container\ContainerInterface;

class AbstractRouterTest extends TestCase
{
    /**
     * @test
     * @expectedException \MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus
     * @expectedExceptionMessage Message name baz not found in route map
     */
    public function it_raise_exception_when_message_not_found(): void
    {
        $router = $this->newRouterInstance(['foo' => 'bar']);

        $router->route('baz');
    }

    /**
     * @test
     * @expectedException \MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus
     * @expectedExceptionMessage Null message handler for message name baz is not allowed
     */
    public function it_raise_exception_when_null_handler_is_not_allowed(): void
    {
        $router = $this->newRouterInstance(['baz' => []]);

        $router->route('baz');
    }

    /**
     * @test
     */
    public function it_generate_message_handler(): void
    {
        $callable = function () {
            $this->assertTrue(true);
        };

        $router = $this->newRouterInstance(['baz' => $callable]);

        $iterable = $router->route('baz');

        foreach ($iterable as $handler) {
            $handler();
        }
    }

    /**
     * @test
     */
    public function it_generate_many_message_handler(): void
    {
        $count = 0;
        $callable = function () use (&$count) {
            $this->assertTrue(true);
            $count++;
        };

        $router = $this->newRouterInstance(['baz' => [$callable, $callable, $callable]]);

        $iterable = $router->route('baz');

        foreach ($iterable as $handler) {
            $handler();
        }

        $this->assertEquals(3, $count);
    }

    /**
     * @test
     * @dataProvider provideInvalidHandlerType
     * @expectedException \MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus
     * @expectedExceptionMessage Message handler for message name baz must be a string, an object or a callable
     */
    public function it_raise_exception_when_handler_type_is_not_supported($invalidType): void
    {
        $router = $this->newRouterInstance(['baz' => $invalidType]);

        $handlers = $router->route('baz');

        foreach ($handlers as $handler) {

        }
    }

    /**
     * @test
     * @dataProvider provideInvalidHandlerType
     * @expectedException \MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus
     * @expectedExceptionMessage No service locator has been set for message handler
     */
    public function it_raise_exception_when_container_no_registered_for_string_handler(): void
    {
        $router = $this->newRouterInstance(['baz' => 'bar']);

        $handlers = $router->route('baz');

        foreach ($handlers as $handler) {
        }
    }

    /**
     * @test
     * @expectedException \Illuminate\Container\EntryNotFoundException
     * @expectedExceptionMessage Service bar not found in container
     */
    public function it_raise_exception_when_container_can_not_locate_service(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('bar')->willReturn(false);

        $router = $this->newRouterInstance(['baz' => 'bar'], $container->reveal());

        $handlers = $router->route('baz');

        foreach ($handlers as $handler) {
        }
    }

    /**
     * @test
     */
    public function it_allow_null_message_handler(): void
    {
        $router = $this->newRouterInstance(['baz' => []], null, true);

        $handlers = $router->route('baz');

        $this->assertCount(0, $handlers);
    }

    public function provideInvalidHandlerType(): array
    {
        return [
            [123], [12.3]
        ];
    }

    private function newRouterInstance(iterable $map,
                                       ?ContainerInterface $container = null,
                                       bool $allowNullHandler = false): AbstractRouter
    {
        return new class($map, $container, $allowNullHandler) extends AbstractRouter
        {
            protected function determineMessageHandler(string $messageName, array $messageHandler): iterable
            {
                foreach ($messageHandler as $handler) {
                    yield $this->resolveMessageHandler($messageName, $handler);
                }
            }
        };
    }
}
