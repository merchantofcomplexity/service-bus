<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Router;

use MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus;

final class SingleHandlerRouter extends AbstractRouter
{
    protected function determineMessageHandler(string $messageName, array $messageHandler): iterable
    {
        if (count($messageHandler) > 1) {
            throw InvalidServiceBus::tooManyHandlers($messageName, SingleHandlerRouter::class);
        }

        yield $this->resolveMessageHandler($messageName, array_shift($messageHandler));
    }
}
