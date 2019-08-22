<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware\Route\Strategy;

use MerchantOfComplexity\Messaging\Contracts\Message;

final class DeferAllAsync extends DeferMessageAsync
{
    public function shouldBeDeferred(Message $message): ?Message
    {
        if ($this->isNotPreviouslyMarkedAsync($message)) {
            return $this->markMessageAsync($message);
        }

        return null;
    }

    public function strategyName(): string
    {
        return self::DEFER_ALL_ASYNC;
    }
}
