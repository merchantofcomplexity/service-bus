<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Async;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\Messaging\Contracts\MessageConverter;
use MerchantOfComplexity\ServiceBus\Support\Concerns\DetectBusType;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Message\MessageProducer;

final class IlluminateProducer implements MessageProducer
{
    use DetectBusType;

    private QueueingDispatcher $dispatcher;
    private MessageConverter $messageConverter;

    public function __construct(QueueingDispatcher $dispatcher, MessageConverter $messageConverter)
    {
        $this->dispatcher = $dispatcher;
        $this->messageConverter = $messageConverter;
    }

    public function __invoke(Message $message): void
    {
        $this->dispatcher->dispatchToQueue($this->toMessageJob($message));
    }

    private function toMessageJob(Message $message): MessageJob
    {
        $payload = $this->messageConverter->convertToArray($message);

        return new MessageJob($payload, $this->detectBusType($message));
    }
}
