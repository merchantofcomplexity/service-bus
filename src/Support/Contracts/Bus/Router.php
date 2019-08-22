<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

interface Router
{
    public function route(string $messageName): iterable;
}
