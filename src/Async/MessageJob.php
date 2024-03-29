<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Async;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Queue;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Messager;

class MessageJob
{
    private array $payload;
    private string $busType;
    public ?string $connection = null;
    private ?string $queue = null;

    public function __construct(array $payload, string $busType, ?string $connection, ?string $queue)
    {
        $this->payload = $payload;
        $this->busType = $busType;
        $this->connection = $connection;
        $this->queue = $queue;
    }

    /**
     * @param Queue $queue
     * @param MessageJob $command
     */
    public function queue(Queue $queue, MessageJob $command): void
    {
        $queue->pushOn($this->queue, $command);
    }

    public function handle(Container $container): void
    {
        /** @var Messager $serviceBus */
        $serviceBus = $container->make($this->busType);

        $serviceBus->dispatch($this->payload);
    }
}
