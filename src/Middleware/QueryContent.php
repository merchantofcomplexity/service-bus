<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware;

use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Middleware;

class QueryContent implements Middleware
{
    public function handle(Envelope $envelope, callable $next)
    {
        /** @var Envelope $envelope */
        $envelope = $next($envelope);

        if ($promise = $envelope->promise()) {
            return $promise;
        }

        return $envelope;
    }
}
