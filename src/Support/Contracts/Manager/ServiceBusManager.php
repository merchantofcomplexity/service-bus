<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Manager;

use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Messager;

interface ServiceBusManager
{
    public function command(?string $name = null): Messager;

    public function query(?string $name = null): Messager;

    public function event(?string $name = null): Messager;

    public function buses(): array;
}
