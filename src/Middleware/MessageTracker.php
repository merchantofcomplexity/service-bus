<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware;

use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Middleware;
use MerchantOfComplexity\ServiceBus\Support\Events\FinalizedEvent;
use Throwable;

final class MessageTracker implements Middleware
{
    public function handle(Envelope $envelope, callable $next)
    {
        $event = $envelope->initialize();

        try {
            $envelope->tracker()->emit($event);

            $envelope = $next($envelope);
        } catch (Throwable $exception) {
            $event->setException($exception);
        } finally {
            $event->stopPropagation(false);

            $event->setEvent(new FinalizedEvent($this));

            $envelope->tracker()->emit($event);
        }

        return $envelope;
    }
}
