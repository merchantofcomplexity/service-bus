<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Middleware\Route;

use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Middleware\Route\AbstractRoute;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageRouteStrategy;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Router;
use MerchantOfComplexityTest\ServiceBus\TestCase;

class AbstractRouteTest extends TestCase
{
    /**
     * @test
     */
    public function it_process_message_instance_async(): void
    {
        $router = $this->prophesize(Router::class);
        $strategy = $this->prophesize(MessageRouteStrategy::class);
        $message = $this->prophesize(Message::class);
        $envelope = $this->prophesize(Envelope::class);

        $envelope->message()->willReturn($message);
        $strategy->shouldBeDeferred($message)->willReturn($message);

        $envelope->wrap($message)->willReturn($envelope);
        $envelope->markMessageReceived()->shouldBeCalled();

        $router->route()->shouldNotBeCalled();

        $route = $this->routeMiddlewareInstance($router->reveal(), $strategy->reveal());

        $envRevealed = $envelope->reveal();

        $receivedEnvelop = $route->handle($envRevealed, function (Envelope $envelope) {
            return $envelope;
        });

        $this->assertEquals($envRevealed, $receivedEnvelop);
    }

    /**
     * @test
     */
    public function it_handle_non_message_instance(): void
    {
        $router = $this->prophesize(Router::class);
        $strategy = $this->prophesize(MessageRouteStrategy::class);
        $envelope = $this->prophesize(Envelope::class);

        $envelope->message()->willReturn('foo');
        $envelope->messageName()->willReturn('foo');

        $strategy->shouldBeDeferred()->shouldNotBeCalled();

        $messageHandler = function ($message) {
            $this->assertEquals('foo', $message);
        };

        $router->route('foo')->will(function () use ($messageHandler) {
            yield $messageHandler;
        });

        $envelope->markMessageReceived()->shouldBeCalled();

        $route = $this->routeMiddlewareInstance($router->reveal(), $strategy->reveal());

        $envRevealed = $envelope->reveal();

        $receivedEnvelop = $route->handle($envRevealed, function (Envelope $envelope) {
            return $envelope;
        });

        $this->assertEquals($envRevealed, $receivedEnvelop);
    }

    /**
     * @test
     */
    public function it_transform_non_callable_to_callable_handler(): void
    {
        $router = $this->prophesize(Router::class);
        $strategy = $this->prophesize(MessageRouteStrategy::class);
        $envelope = $this->prophesize(Envelope::class);

        $envelope->message()->willReturn('foo');
        $envelope->messageName()->willReturn('foo');

        $strategy->shouldBeDeferred()->shouldNotBeCalled();


        $test = $this;
        $nonCallableMessageHandler = new class($test)
        {
            private $test;

            public function __construct(\PHPUnit\Framework\TestCase $test)
            {
                $this->test = $test;
            }

            public function onEvent($message)
            {
                $this->test->assertEquals('foo', $message);
            }
        };

        $toCallable = function ($handler) {
            return \Closure::fromCallable([$handler, 'onEvent']);
        };

        $router->route('foo')->will(function () use ($nonCallableMessageHandler) {
            yield $nonCallableMessageHandler;
        });

        $envelope->markMessageReceived()->shouldBeCalled();

        $route = $this->routeMiddlewareInstance($router->reveal(), $strategy->reveal(), $toCallable);

        $envRevealed = $envelope->reveal();

        $receivedEnvelop = $route->handle($envRevealed, function (Envelope $envelope) {
            return $envelope;
        });

        $this->assertEquals($envRevealed, $receivedEnvelop);
    }

    /**
     * @test
     * @expectedException \MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus
     * @expectedExceptionMessage Message handler must be a callable:
     */
    public function it_raise_exception_if_message_handler_is_not_callable(): void
    {
        $router = $this->prophesize(Router::class);
        $strategy = $this->prophesize(MessageRouteStrategy::class);
        $envelope = $this->prophesize(Envelope::class);

        $envelope->message()->willReturn('foo');
        $envelope->messageName()->willReturn('foo');

        $strategy->shouldBeDeferred()->shouldNotBeCalled();


        $test = $this;
        $nonCallableMessageHandler = new class($test)
        {
            private $test;

            public function __construct(\PHPUnit\Framework\TestCase $test)
            {
                $this->test = $test;
            }

            public function onEvent($message)
            {
                $this->test->assertEquals('foo', $message);
            }
        };

        $router->route('foo')->will(function () use ($nonCallableMessageHandler) {
            yield $nonCallableMessageHandler;
        });

        $envelope->markMessageReceived()->shouldNotBeCalled();

        $route = $this->routeMiddlewareInstance($router->reveal(), $strategy->reveal(), null);

        $envRevealed = $envelope->reveal();

        $route->handle($envRevealed, function (Envelope $envelope) {
            return $envelope;
        });
    }

    /**
     * @@test
     * @expectedException \MerchantOfComplexity\ServiceBus\Exception\MessageCollection
     * @expectedExceptionMessage At least one event listener caused an exception.Check listener exceptions for details:
    foo
    foo
     */
    public function it_collect_exceptions(): void
    {
        $router = $this->prophesize(Router::class);
        $strategy = $this->prophesize(MessageRouteStrategy::class);
        $envelope = $this->prophesize(Envelope::class);

        $envelope->message()->willReturn('foo');
        $envelope->messageName()->willReturn('foo');

        $strategy->shouldBeDeferred()->shouldNotBeCalled();

        $messageHandler = function(){
            throw new \RuntimeException("foo");
        };

        $router->route('foo')->will(function () use ($messageHandler) {
            yield $messageHandler;
            yield $messageHandler;
        });

        $envelope->markMessageReceived()->shouldNotBeCalled();

        $route = $this->routeMiddlewareInstance(
            $router->reveal(), $strategy->reveal(), null, true
        );

        $envRevealed = $envelope->reveal();

        $route->handle($envRevealed, function (Envelope $envelope) {
            return $envelope;
        });
    }

    private function routeMiddlewareInstance(Router $router,
                                             MessageRouteStrategy $strategy,
                                             ?callable $toCallable = null,
                                             bool $collectException = false): AbstractRoute
    {
        return new class($router, $strategy, $toCallable, $collectException) extends AbstractRoute
        {
            protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope
            {
                $messageHandler($envelope->message());

                return $envelope;
            }
        };
    }
}
