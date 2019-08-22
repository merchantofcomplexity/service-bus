<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Router;

use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Router;

final class EventRouter implements Router
{
    private MultipleHandlerRouter $router;

    public function __construct(MultipleHandlerRouter $router)
    {
        $this->router = $router;
    }

    public function route(string $messageName): iterable
    {
        return $this->router->route($messageName);
    }
}
