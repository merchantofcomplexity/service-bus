<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Support\Events;

use MerchantOfComplexity\Messaging\Contracts\MessageFactory;
use MerchantOfComplexity\Messaging\DomainMessage;
use MerchantOfComplexity\Messaging\Supports\Concerns\HasPayloadConstructor;
use MerchantOfComplexity\ServiceBus\Support\Events\DispatchedEvent;
use MerchantOfComplexity\ServiceBus\Support\Events\FQCNMessageSubscriber;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexityTest\ServiceBus\TestCase;
use Prophecy\Argument;
use function get_class;

class FQCNMessageSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_subscribe_to_dispatched_event(): void
    {
        $factory = $this->prophesize(MessageFactory::class);

        $event = new FQCNMessageSubscriber($factory->reveal());

        $this->assertInstanceOf(DispatchedEvent::class, $event->subscribeTo());
    }

    /**
     * @test
     */
    public function it_convert_array_message_to_message_instance(): void
    {
        $factory = $this->prophesize(MessageFactory::class);
        $actionEvent = $this->prophesize(ActionEvent::class);

        $message = [FQCNMessageSubscriber::MESSAGE_NAME_KEY => 'foo', 'payload' => ['baz']];

        $actionEvent->message()->willReturn($message);

        $messageInstance = new class() extends DomainMessage
        {
            use HasPayloadConstructor;

            public function messageType(): string
            {
                return 'foo_bar';
            }
        };

        $messageInstanceName = get_class($messageInstance);

        $factory->createMessageFromArray('foo', ['payload' => ['baz']])->willReturn($messageInstance);

        $actionEvent->setMessage(Argument::type(DomainMessage::class))->shouldBeCalled();
        $actionEvent->setMessageName($messageInstanceName)->shouldBeCalled();

        $event = new FQCNMessageSubscriber($factory->reveal());

        $event->applyTo()($actionEvent->reveal());
    }

    /**
     * @test
     */
    public function it_does_not_convert_array_message_if_message_name_key_does_not_exists(): void
    {
        $factory = $this->prophesize(MessageFactory::class);
        $actionEvent = $this->prophesize(ActionEvent::class);

        $message =['foo' => 'bar'];
        $actionEvent->message()->willReturn($message);

        $factory->createMessageFromArray()->shouldNotBeCalled();
        $actionEvent->setMessage()->shouldNotBeCalled();
        $actionEvent->setMessageName()->shouldNotBeCalled();

        $event = new FQCNMessageSubscriber($factory->reveal());

        $event->applyTo()($actionEvent->reveal());
    }

    /**
     * @test
     * @dataProvider provideUnsupportedMessage
     */
    public function it_only_convert_array_message($message): void
    {
        $factory = $this->prophesize(MessageFactory::class);
        $actionEvent = $this->prophesize(ActionEvent::class);

        $actionEvent->message()->willReturn($message);

        $factory->createMessageFromArray()->shouldNotBeCalled();
        $actionEvent->setMessage()->shouldNotBeCalled();
        $actionEvent->setMessageName()->shouldNotBeCalled();

        $event = new FQCNMessageSubscriber($factory->reveal());

        $event->applyTo()($actionEvent->reveal());
    }

    public function provideUnsupportedMessage(): array
    {
        return [
            ['foo'], [new \stdClass()], [$this->messageInstance()]
        ];
    }

    private function messageInstance(): DomainMessage
    {
        return new class() extends DomainMessage
        {
            use HasPayloadConstructor;

            public function messageType(): string
            {
                return 'foo_bar';
            }
        };
    }
}
