<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware\Route\Strategy;

use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Message\AsyncMessage;

final class DeferOnlyMarkedAsync extends DeferMessageAsync
{
    public function shouldBeDeferred(Message $message): ?Message
    {
        if ($message instanceof AsyncMessage && $this->isNotPreviouslyMarkedAsync($message)) {
            return $this->markMessageAsync($message);
        }

        return null;
    }

    public function strategyName(): string
    {
        return self::DEFER_ONLY_MARKED_ASYNC;
    }
}
