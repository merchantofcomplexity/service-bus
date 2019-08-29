<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Manager;

use MerchantOfComplexity\ServiceBus\CommandBus;
use MerchantOfComplexity\ServiceBus\EventBus;
use MerchantOfComplexity\ServiceBus\QueryBus;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Messager;

final class DefaultBusManager extends ServiceBusManager
{
    public function command(?string $name = null): Messager
    {
        return $this->make($name ?? 'default', CommandBus::class);
    }

    public function query(?string $name = null): Messager
    {
        return $this->make($name ?? 'default', QueryBus::class);
    }

    public function event(?string $name = null): Messager
    {
        return $this->make($name ?? 'default', EventBus::class);
    }

    public function buses(): array
    {
        return $this->buses;
    }
}
