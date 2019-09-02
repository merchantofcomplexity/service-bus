<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

use Closure;
use MerchantOfComplexity\ServiceBus\Envelope;

interface Middleware
{
    /**
     * Handle envelope stack
     *
     * @param Envelope $envelope
     * @param callable $next
     * @return Closure|Envelope
     */
    public function handle(Envelope $envelope, callable $next);
}
