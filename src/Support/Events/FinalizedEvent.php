<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Events;

use MerchantOfComplexity\Tracker\AbstractNamedEvent;

final class FinalizedEvent extends AbstractNamedEvent
{
    public function name(): string
    {
        return static::class;
    }
}
