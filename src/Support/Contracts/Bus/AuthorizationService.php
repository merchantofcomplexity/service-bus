<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

interface AuthorizationService
{
    public function isGranted(string $messageName, object $context = null): bool;
}
