<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Support\Events;

use MerchantOfComplexity\ServiceBus\Support\Events\DetectMessageNameSubscriber;
use MerchantOfComplexity\ServiceBus\Support\Events\DispatchedEvent;
use MerchantOfComplexity\ServiceBus\Support\Events\ExceptionSubscriber;
use MerchantOfComplexity\ServiceBus\Support\Events\FinalizedEvent;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexityTest\ServiceBus\TestCase;

class ExceptionSubscriberTest extends TestCase
{
    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage foo
     */
    public function it_raise_exception_if_exception_exist_on_action_event(): void
    {
        $exception = new \RuntimeException("foo");

        $event = new ExceptionSubscriber();
        $actionEvent = $this->prophesize(ActionEvent::class);

        $actionEvent->exception()->willReturn($exception);

        $event->applyTo()($actionEvent->reveal());
    }

    /**
     * @test
     */
    public function it_subscribe_on_finalized_event(): void
    {
        $event = new ExceptionSubscriber();

        $this->assertInstanceOf(FinalizedEvent::class, $event->subscribeTo());
    }
}
