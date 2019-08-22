<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

interface Messager
{
    /**
     * Dispatch message
     *
     * @param mixed $message
     * @return mixed
     */
    public function dispatch($message);
}
