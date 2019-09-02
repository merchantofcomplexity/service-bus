<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit;

use Illuminate\Contracts\Container\Container;
use MerchantOfComplexity\Messaging\Command;
use MerchantOfComplexity\Messaging\Factory\FQCNMessageFactory;
use MerchantOfComplexity\Messaging\Supports\Concerns\HasPayloadConstructor;
use MerchantOfComplexity\ServiceBus\CommandBus;
use MerchantOfComplexity\ServiceBus\Exception\RuntimeException;
use MerchantOfComplexity\ServiceBus\Middleware\MessageTracker;
use MerchantOfComplexity\ServiceBus\Middleware\Route\CommandRoute;
use MerchantOfComplexity\ServiceBus\Middleware\Route\Strategy\DeferNoneAsync;
use MerchantOfComplexity\ServiceBus\Router\CommandRouter;
use MerchantOfComplexity\ServiceBus\Router\SingleHandlerRouter;
use MerchantOfComplexity\ServiceBus\Support\Events\DispatchedEvent;
use MerchantOfComplexity\ServiceBus\Support\Events\ExceptionSubscriber;
use MerchantOfComplexity\ServiceBus\Support\Events\FinalizedEvent;
use MerchantOfComplexity\ServiceBus\Support\Events\FQCNMessageSubscriber;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexity\Tracker\Contracts\NamedEvent;
use MerchantOfComplexity\Tracker\Contracts\SubscribedEvent;
use MerchantOfComplexity\Tracker\Contracts\Tracker;
use MerchantOfComplexity\Tracker\DefaultTracker;
use MerchantOfComplexityTest\ServiceBus\TestCase;
use function get_class;

class CommandBusTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_message(): void
    {
        $map = [
            'foo' => function ($message) {
                $this->assertEquals('foo', $message);
            },
        ];

        $bus = new CommandBus($this->defaultMiddleware($map), $this->defaultTracker());

        $bus->dispatch('foo');
    }

    /**
     * @test
     */
    public function it_dispatch_message_command(): void
    {
        $command = new class() extends Command
        {
            use HasPayloadConstructor;
        };

        $commandClass = get_class($command);

        $map = [
            $commandClass => function ($receivedCommand) use ($command) {
                $this->assertEquals($command, $receivedCommand);
            },
        ];

        $bus = new CommandBus($this->defaultMiddleware($map), $this->defaultTracker());

        $bus->dispatch($command);
    }

    /**
     * @test
     */
    public function it_resolve_handler_string_through_container(): void
    {
        $container = new \Illuminate\Container\Container();

        $container->bind('service-foo', function () {
            return function ($message) {
                $this->assertEquals('foo', $message);
            };
        });

        $map = ['foo' => 'service-foo'];

        $bus = new CommandBus($this->defaultMiddleware($map, $container), $this->defaultTracker());

        $bus->dispatch('foo');
    }

    /**
     * @test
     */
    public function it_subscribe_to_bus_tracker(): void
    {
        $command = new class() extends Command
        {
            use HasPayloadConstructor;
        };

        $commandClass = get_class($command);

        $map = [
            $commandClass => function ($receivedCommand) use ($command) {
            },
        ];

        $testCase = $this;
        $subscriber = new class($testCase, $commandClass) implements SubscribedEvent{
            private $testCase;
            private string $commandClass;

            public function __construct($testCase, string $commandClass)
            {
                $this->testCase = $testCase;
                $this->commandClass = $commandClass;
            }

            public function priority(): int
            {
               return 100000;
            }

            public function subscribeTo(): NamedEvent
            {
               return new DispatchedEvent();
            }

            public function applyTo(): callable
            {
                return function(ActionEvent $event): void{
                    $this->testCase->assertEquals($event->messageName(), $this->commandClass);
                };
            }
        };

        $bus = new CommandBus($this->defaultMiddleware($map), $this->defaultTracker());
        $bus->subscribe($subscriber);

        $bus->dispatch($command);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Message name bar not found in route map
     */
    public function it_raise_exception_if_message_not_found_in_map(): void
    {
        $bus = new CommandBus($this->defaultMiddleware($map = []), $this->defaultTracker());

        $bus->dispatch('bar');
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Null message handler for message name foo is not allowed
     */
    public function it_raise_exception_if_message_handler_is_empty(): void
    {
        $bus = new CommandBus($this->defaultMiddleware(['foo' => []]), $this->defaultTracker());

        $bus->dispatch('foo');
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Router handler MerchantOfComplexity\ServiceBus\Router\SingleHandlerRouter can route to one handler only for message name foo
     */
    public function it_raise_exception_if_multiple_message_handlers_has_been_set(): void
    {
        $bus = new CommandBus($this->defaultMiddleware(['foo' => ['bar', 'foo_bar']]), $this->defaultTracker());

        $bus->dispatch('foo');
    }

    private function defaultTracker(): Tracker
    {
        $tracker = new DefaultTracker();

        $tracker->subscribe(new DispatchedEvent());
        $tracker->subscribe(new FinalizedEvent());
        $tracker->subscribe(new FQCNMessageSubscriber(new FQCNMessageFactory()));
        $tracker->subscribe(new ExceptionSubscriber());

        return $tracker;
    }

    private function defaultMiddleware(array $map, Container $container = null): array
    {
        return [
            new MessageTracker(),
            new CommandRoute(
                new CommandRouter(new SingleHandlerRouter($map, $container)),
                new DeferNoneAsync()
            )
        ];
    }
}
