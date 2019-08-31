<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Support\Events;

use MerchantOfComplexity\ServiceBus\Support\Events\InitializedSubscriber;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexityTest\ServiceBus\TestCase;

class InitializedSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_initialize(): void
    {
        $action = $this->prophesize(ActionEvent::class);
        $action->setMessageHandled(false)->shouldBeCalled();

        $action->message()->willReturn('foo');
        $action->setMessageName('foo')->shouldBeCalled();

        $event = new InitializedSubscriber();

        $event->applyTo()($action->reveal());
    }
}
