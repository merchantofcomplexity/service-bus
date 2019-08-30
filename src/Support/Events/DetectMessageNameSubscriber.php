<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Events;

use MerchantOfComplexity\ServiceBus\Support\Concerns\DetectMessageName;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexity\Tracker\Contracts\NamedEvent;
use MerchantOfComplexity\Tracker\Contracts\SubscribedEvent;

final class DetectMessageNameSubscriber implements SubscribedEvent
{
    use DetectMessageName;

    public function priority(): int
    {
        return 1000;
    }

    public function subscribeTo(): NamedEvent
    {
        return new DispatchedEvent();
    }

    public function applyTo(): callable
    {
        return function (ActionEvent $event): void {
            $event->setMessageName(
                $this->detectMessageName($event->message())
            );
        };
    }
}
