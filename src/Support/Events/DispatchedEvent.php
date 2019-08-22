<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Events;

use MerchantOfComplexity\Tracker\AbstractNamedEvent;

final class DispatchedEvent extends AbstractNamedEvent
{
    public function name(): string
    {
        return static::class;
    }
}
