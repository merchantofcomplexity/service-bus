<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Router;

use MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus;
use MerchantOfComplexity\ServiceBus\Exception\RuntimeException;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Router;
use Psr\Container\ContainerInterface;
use function gettype;

abstract class AbstractRouter implements Router
{
    private iterable $map;
    private ?ContainerInterface $container;
    private bool $allowNullMessageHandler;

    public function __construct(iterable $map,
                                ContainerInterface $container = null,
                                bool $allowNullMessageHandler = false)
    {
        $this->map = $map;
        $this->container = $container;
        $this->allowNullMessageHandler = $allowNullMessageHandler;
    }

    public function route(string $messageName): iterable
    {
        if (!isset($this->map[$messageName])) {
            throw new RuntimeException("Message name $messageName not found in route map");
        }

        $messageHandler = $this->map[$messageName];

        if (!is_array($messageHandler)) {
            $messageHandler = [$messageHandler];
            $messageHandler = array_filter($messageHandler);
        }

        if (!$messageHandler) {
            if ($this->allowNullMessageHandler) {
                return [];
            }

            throw InvalidServiceBus::nullMessageHandlerNotAllowed($messageName);
        }

        return $this->determineMessageHandler($messageName, $messageHandler);
    }

    abstract protected function determineMessageHandler(string $messageName, array $messageHandler): iterable;

    /**
     * @param string $messageName
     * @param $messageHandler
     * @return object
     */
    protected function resolveMessageHandler(string $messageName, $messageHandler): object
    {
        if (!$this->isMessageHandlerTypeSupported($messageHandler)) {
            throw InvalidServiceBus::invalidMessageHandlerType($messageName, gettype($messageHandler));
        }

        if (is_string($messageHandler)) {
            if ($this->container) {
                return $this->container->get($messageHandler);
            }

            throw InvalidServiceBus::invalidContainer($messageHandler, $messageName);
        }

        return $messageHandler;
    }

    /**
     * @param $messageHandler
     * @return bool
     */
    protected function isMessageHandlerTypeSupported($messageHandler): bool
    {
        return !(!is_object($messageHandler) && !is_callable($messageHandler) && !is_string($messageHandler));
    }
}
