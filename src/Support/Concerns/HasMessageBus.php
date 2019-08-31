<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Concerns;

use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\Tracker\Contracts\Event;
use MerchantOfComplexity\Tracker\Contracts\SubscribedEvent;
use MerchantOfComplexity\Tracker\Contracts\Tracker;

trait HasMessageBus
{
    private iterable $map;
    private Tracker $tracker;

    public function __construct(iterable $middleware, Tracker $tracker)
    {
        $this->map = $middleware;
        $this->tracker = $tracker;
    }

    protected function dispatchForBus(string $busType, $message)
    {
        $envelope = new Envelope($this->tracker, $busType, $message);

        return $this->dispatchMessage($envelope);
    }

    private function dispatchMessage(Envelope $envelope)
    {
        return call_user_func($this->nextMiddleware(0, $envelope), $envelope);
    }

    private function nextMiddleware(int $index, Envelope $currentEnvelope): callable
    {
        if (null === $this->map) {
            $this->map = [];
        }

        if (!isset($this->map[$index])) {
            return function (Envelope $envelope) {
                return $envelope;
            };
        }

        $middleware = $this->map[$index];

        return function (Envelope $envelope) use ($middleware, $index, $currentEnvelope) {
            return $middleware->handle(
                $envelope,
                $this->nextMiddleware($index + 1, $currentEnvelope)
            );
        };
    }

    public function subscribe(Event $event): Event
    {
        return $this->tracker->subscribe($event);
    }

    public function unsubscribe(SubscribedEvent $event): bool
    {
        return $this->tracker->unsubscribe($event);
    }
}
