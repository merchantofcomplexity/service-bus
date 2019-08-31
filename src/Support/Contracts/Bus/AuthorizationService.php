<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

interface AuthorizationService
{
    /**
     * Grant access
     *
     * @param string $messageName
     * @param object|null $context
     * @return bool
     */
    public function isGranted(string $messageName, object $context = null): bool;
}
