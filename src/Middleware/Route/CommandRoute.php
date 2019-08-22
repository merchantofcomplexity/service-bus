<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware\Route;

use MerchantOfComplexity\ServiceBus\Envelope;

final class CommandRoute extends AbstractRoute
{
    protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope
    {
        $messageHandler($envelope->message());

        return $envelope;
    }
}
