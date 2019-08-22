<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware\Route\Strategy;

use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageRouteStrategy;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Message\MessageProducer;

abstract class DeferMessageAsync implements MessageRouteStrategy
{
    private MessageProducer $messageProducer;

    public function __construct(MessageProducer $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    /**
     * Mark message async
     *
     * @param Message $message
     * @return Message
     */
    protected function markMessageAsync(Message $message): Message
    {
        if (!$this->isNotPreviouslyMarkedAsync($message)) {
            throw InvalidServiceBus::messageAlreadyProducedAsync($message->messageName());
        }

        $message = $message->withAddedMetadata(self::ASYNC_METADATA_KEY, true);

        ($this->messageProducer)($message);

        return $message;
    }

    protected function isNotPreviouslyMarkedAsync(Message $message): bool
    {
        return false === ($message->metadata()[self::ASYNC_METADATA_KEY] ?? false);
    }
}
