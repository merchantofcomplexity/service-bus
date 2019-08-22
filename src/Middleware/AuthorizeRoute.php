<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware;

use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Exception\AuthorizationDenied;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\AuthorizationService;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Middleware;

final class AuthorizeRoute implements Middleware
{
    private AuthorizationService $authorization;

    public function __construct(AuthorizationService $authorization)
    {
        $this->authorization = $authorization;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        $message = $envelope->message();

        $context = is_object($message) ? $message : null;

        if (!$this->authorization->isGranted($envelope->messageName(), $context)) {
            throw new AuthorizationDenied("You are not authorized to a access the resource");
        }

        return $next($envelope);
    }
}
