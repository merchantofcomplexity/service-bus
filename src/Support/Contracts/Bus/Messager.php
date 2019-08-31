<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

use MerchantOfComplexity\Tracker\Contracts\Event;
use MerchantOfComplexity\Tracker\Contracts\SubscribedEvent;

interface Messager
{
    /**
     * Dispatch message
     *
     * @param mixed $message
     * @return mixed
     */
    public function dispatch($message);

    /**
     * @param Event $event
     * @return Event
     */
    public function subscribe(Event $event): Event;

    /**
     * @param SubscribedEvent $event
     * @return bool
     */
    public function unsubscribe(SubscribedEvent $event): bool;
}
