<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Middleware\Route;

use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Middleware\MessageTracker;
use MerchantOfComplexity\ServiceBus\Support\Events\FinalizedEvent;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexity\Tracker\Contracts\Tracker;
use MerchantOfComplexityTest\ServiceBus\TestCase;
use Prophecy\Argument;

class MessageTrackerTest extends TestCase
{
    /**
     * @test
     */
    public function it_handle_message(): void
    {
        $envelope = $this->prophesize(Envelope::class);
        $actionEvent = $this->prophesize(ActionEvent::class);
        $tracker = $this->prophesize(Tracker::class);

        $envelope->initialize()->willReturn($actionEvent);
        $tracker->emit($actionEvent)->shouldBeCalled();
        $envelope->tracker()->willReturn($tracker);

        $actionEvent->stopPropagation(false)->shouldBeCalled();
        $actionEvent->setEvent(Argument::type(FinalizedEvent::class))->shouldBeCalled();

        $tracker->emit(Argument::type(FinalizedEvent::class));

        $middleware = new MessageTracker();

        $response = $middleware->handle($env = $envelope->reveal(), function (Envelope $env) {
            return $env;
        });

        $this->assertEquals($env, $response);
    }

    /**
     * @test
     */
    public function it_catch_exception_and_set_on_action_event(): void
    {
        $envelope = $this->prophesize(Envelope::class);
        $actionEvent = $this->prophesize(ActionEvent::class);
        $tracker = $this->prophesize(Tracker::class);

        $envelope->initialize()->willReturn($actionEvent);
        $tracker->emit($actionEvent)->shouldBeCalled();
        $envelope->tracker()->willReturn($tracker);

        $exception = new \RuntimeException("foo");
        $actionEvent->setException($exception)->shouldBeCalled();

        $actionEvent->stopPropagation(false)->shouldBeCalled();
        $actionEvent->setEvent(Argument::type(FinalizedEvent::class))->shouldBeCalled();

        $middleware = new MessageTracker();

        $middleware->handle($env = $envelope->reveal(), function (Envelope $env) use ($exception) {
            throw $exception;
        });
    }
}
