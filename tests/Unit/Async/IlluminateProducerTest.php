<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Async;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use MerchantOfComplexity\Messaging\Command;
use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\Messaging\Contracts\MessageConverter;
use MerchantOfComplexity\Messaging\DomainMessage;
use MerchantOfComplexity\Messaging\Supports\Concerns\HasPayloadConstructor;
use MerchantOfComplexity\ServiceBus\Async\IlluminateProducer;
use MerchantOfComplexity\ServiceBus\Async\MessageJob;
use MerchantOfComplexityTest\ServiceBus\TestCase;
use Prophecy\Argument;

class IlluminateProducerTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_message_to_illuminate_queue(): void
    {
        $queue = $this->prophesize(QueueingDispatcher::class);

        $converter = $this->prophesize(MessageConverter::class);

        $message = new class extends Command{
            use HasPayloadConstructor;
        };

        $converter->convertToArray($message)->willReturn(['foo'])->shouldBeCalled();

        $queue->dispatchToQueue(Argument::type(MessageJob::class))->shouldBeCalled();

        $producer = new IlluminateProducer($queue->reveal(), $converter->reveal());

        $producer($message);
    }

    /**
     * @test
     * @expectedException \MerchantOfComplexity\ServiceBus\Exception\RuntimeException
     * @expectedExceptionMessage Unknown bus type
     */
    public function it_raise_exception_if_bus_type_is_unknown(): void
    {
        $queue = $this->prophesize(QueueingDispatcher::class);

        $converter = $this->prophesize(MessageConverter::class);

        $message = new class extends DomainMessage {
            use HasPayloadConstructor;
            public function messageType(): string
            {
               return 'foo';
            }
        };

        $converter->convertToArray($message)->willReturn(['foo'])->shouldBeCalled();

        $queue->dispatchToQueue(Argument::type(MessageJob::class))->shouldNotBeCalled();

        $producer = new IlluminateProducer($queue->reveal(), $converter->reveal());

        $producer($message);
    }
}
