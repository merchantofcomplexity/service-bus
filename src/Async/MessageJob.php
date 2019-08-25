<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Async;

use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Messager;
use Psr\Container\ContainerInterface;

class MessageJob
{
    private array $payload;
    private string $busType;

    public function __construct(array $payload, string $busType)
    {
        $this->payload = $payload;
        $this->busType = $busType;
    }

    public function handle(ContainerInterface $container): void
    {
        /** @var Messager $serviceBus */
        $serviceBus = $container->get($this->busType);

        $serviceBus->dispatch($this->payload);
    }
}