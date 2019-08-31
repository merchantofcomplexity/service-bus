<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Support\Events;

use MerchantOfComplexity\ServiceBus\Support\Events\DetectMessageNameSubscriber;
use MerchantOfComplexity\ServiceBus\Support\Events\DispatchedEvent;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexityTest\ServiceBus\TestCase;

class DetectMessageNameSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_set_message_name_on_action_event(): void
    {
        $event = new DetectMessageNameSubscriber();

        $actionEvent = $this->prophesize(ActionEvent::class);

        $actionEvent->message()->willReturn('foo')->shouldBeCalledOnce();
        $actionEvent->setMessageName('foo')->shouldBeCalledOnce();

        $event->applyTo()($actionEvent->reveal());
    }

    /**
     * @test
     */
    public function it_subscribe_on_dispatched_event(): void
    {
        $event = new DetectMessageNameSubscriber();

        $this->assertInstanceOf(DispatchedEvent::class, $event->subscribeTo());
    }
}
