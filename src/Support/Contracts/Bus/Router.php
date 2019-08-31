<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

use Illuminate\Container\EntryNotFoundException;
use MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus;

interface Router
{
    /**
     * Route message to his handler(s)
     *
     * @param string $messageName
     * @return iterable
     * @throws InvalidServiceBus
     * @throws EntryNotFoundException
     */
    public function route(string $messageName): iterable;
}
