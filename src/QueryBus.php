<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus;

use MerchantOfComplexity\ServiceBus\Support\Concerns\HasMessageBus;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageTracker;
use React\Promise\PromiseInterface;

class QueryBus implements MessageTracker
{
    use HasMessageBus;

    public function dispatch($message): PromiseInterface
    {
        return $this->dispatchForBus(static::class, $message);
    }
}
