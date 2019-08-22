<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Events;

use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexity\Tracker\Contracts\NamedEvent;
use MerchantOfComplexity\Tracker\Contracts\SubscribedEvent;

final class ExceptionSubscriber implements SubscribedEvent
{
    public function priority(): int
    {
        return 1;
    }

    public function subscribeTo(): NamedEvent
    {
        return new FinalizedEvent();
    }

    public function applyTo(): callable
    {
        return function (ActionEvent $event) {
            if ($exception = $event->exception()) {
                throw $exception;
            }
        };
    }
}
