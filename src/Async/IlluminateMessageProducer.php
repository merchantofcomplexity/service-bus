<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Async;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\Queue;
use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\Messaging\Contracts\MessageConverter;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Message\MessageProducer;

final class IlluminateMessageProducer implements MessageProducer
{
    private QueueingDispatcher $queueingDispatcher;
    private MessageConverter $messageConverter;
    private string $busClass;
    private ?string $connection;
    private ?string $queue;

    public function __construct(QueueingDispatcher $queueingDispatcher,
                                MessageConverter $messageConverter,
                                string $busClass,
                                ?string $connection = null,
                                ?string $queue = null)
    {
        $this->queueingDispatcher = $queueingDispatcher;
        $this->messageConverter = $messageConverter;
        $this->busClass = $busClass;
        $this->connection = $connection;
        $this->queue = $queue;
    }

    public function __invoke(Message $message): void
    {
        $message = $this->messageConverter->convertToArray($message);

        $this->queueingDispatcher->dispatchToQueue(
            new MessageJob($message, $this->busClass, $this->connection, $this->queue)
        );
    }
}
