<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Concerns;

use MerchantOfComplexity\Messaging\Command;
use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\Messaging\DomainEvent;
use MerchantOfComplexity\Messaging\Query;
use MerchantOfComplexity\ServiceBus\CommandBus;
use MerchantOfComplexity\ServiceBus\EventBus;
use MerchantOfComplexity\ServiceBus\Exception\RuntimeException;
use MerchantOfComplexity\ServiceBus\QueryBus;

trait DetectBusType
{
    protected function detectBusType(Message $message): string
    {
        switch ($message) {
            case  $message instanceof Command:
                return CommandBus::class;
            case  $message instanceof Query:
                return QueryBus::class;
            case  $message instanceof DomainEvent:
                return EventBus::class;
        }

        throw new RuntimeException(
            "Unknown bus type {$message->messageType()} for message name {$message->messageName()}"
        );
    }
}
