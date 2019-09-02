<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Manager;

use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Messager;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageTracker;

interface ServiceBusManager
{
    /**
     * Command bus
     *
     * @param string|null $name
     * @return Messager|MessageTracker
     */
    public function command(?string $name = null): Messager;

    /**
     * Query bus
     *
     * @param string|null $name
     * @return Messager|MessageTracker
     */
    public function query(?string $name = null): Messager;

    /**
     * Event bus
     *
     * @param string|null $name
     * @return Messager|MessageTracker
     */
    public function event(?string $name = null): Messager;

    /**
     * Return registered buses
     *
     * @return array
     */
    public function buses(): array;
}
