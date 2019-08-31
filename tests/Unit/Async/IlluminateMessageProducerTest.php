<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Async;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use MerchantOfComplexity\Messaging\Command;
use MerchantOfComplexity\Messaging\Contracts\MessageConverter;
use MerchantOfComplexity\Messaging\Supports\Concerns\HasPayloadConstructor;
use MerchantOfComplexity\ServiceBus\Async\IlluminateMessageProducer;
use MerchantOfComplexity\ServiceBus\Async\MessageJob;
use MerchantOfComplexityTest\ServiceBus\TestCase;
use Prophecy\Argument;

class IlluminateMessageProducerTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_message_to_illuminate_queue(): void
    {
        $queue = $this->prophesize(QueueingDispatcher::class);
        $converter = $this->prophesize(MessageConverter::class);

        $message = new class extends Command
        {
            use HasPayloadConstructor;
        };

        $converter->convertToArray($message)->willReturn(['foo'])->shouldBeCalled();

        // todo enhance test message job on queue bus type connection
        $queue->dispatchToQueue(Argument::type(MessageJob::class))->shouldBeCalled();

        $producer = new IlluminateMessageProducer(
            $queue->reveal(), $converter->reveal(), 'bus_type', 'connection', 'queue'
        );

        $producer($message);
    }
}
