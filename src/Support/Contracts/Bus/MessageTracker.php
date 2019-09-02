<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

use MerchantOfComplexity\Tracker\Contracts\Event;
use MerchantOfComplexity\Tracker\Contracts\SubscribedEvent;

interface MessageTracker extends Messager
{
    /**
     * Subscribe to bus tracker
     *
     * @param Event $event
     * @return Event
     */
    public function subscribe(Event $event): Event;

    /**
     * Unsubscribe to bus tracker
     *
     * @param SubscribedEvent $event
     * @return bool
     */
    public function unsubscribe(SubscribedEvent $event): bool;
}
