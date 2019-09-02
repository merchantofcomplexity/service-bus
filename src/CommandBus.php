<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus;

use MerchantOfComplexity\ServiceBus\Support\Concerns\HasMessageBus;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageTracker;

class CommandBus implements MessageTracker
{
    use HasMessageBus;

    public function dispatch($message): void
    {
        $this->dispatchForBus(static::class, $message);
    }
}
