<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Router;

use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Router;

final class CommandRouter implements Router
{
    private SingleHandlerRouter $router;

    public function __construct(SingleHandlerRouter $router)
    {
        $this->router = $router;
    }

    public function route(string $messageName): iterable
    {
        return $this->router->route($messageName);
    }
}
