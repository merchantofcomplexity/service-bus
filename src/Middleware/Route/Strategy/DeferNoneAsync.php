<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware\Route\Strategy;

use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageRouteStrategy;

final class DeferNoneAsync implements MessageRouteStrategy
{
    public function shouldBeDeferred(Message $message): ?Message
    {
        return null;
    }

    public function strategyName(): string
    {
        return self::DEFER_NONE_ASYNC;
    }
}
