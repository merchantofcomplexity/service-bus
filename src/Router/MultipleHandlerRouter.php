<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Router;

final class MultipleHandlerRouter extends AbstractRouter
{
    protected function determineMessageHandler(string $messageName, array $messageHandler): iterable
    {
        foreach ($messageHandler as $handler) {
            yield $this->resolveMessageHandler($messageName, $handler);
        }
    }
}
