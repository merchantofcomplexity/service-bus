<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware\Route\Handler;

use Closure;
use MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus;
use function get_class;

class CallableHandler
{
    private string $methodName;

    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
    }

    public function __invoke(object $messageHandler): callable
    {
        if (!is_callable([$messageHandler, $this->methodName])) {
            throw InvalidServiceBus::missingCallableMethodName(get_class($messageHandler), $this->methodName);
        }

        return Closure::fromCallable([$messageHandler, $this->methodName]);
    }
}
