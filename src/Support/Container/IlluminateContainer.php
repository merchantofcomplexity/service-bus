<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Container;

use Illuminate\Contracts\Container\Container;
use Psr\Container\ContainerInterface;

class IlluminateContainer implements ContainerInterface
{
    private Container $container;
    private bool $resolveServiceDynamically;

    public function __construct(Container $container, bool $resolveServiceDynamically = true)
    {
        $this->container = $container;
        $this->resolveServiceDynamically = $resolveServiceDynamically;
    }

    public function get($id)
    {
        if ($this->has($id)) {
            return $this->container->make($id);
        }

        throw new ServiceNotFound("Service id not found: $id");
    }

    public function has($id): bool
    {
        if ($this->container->bound($id)) {
            return true;
        }

        if (class_exists($id) && $this->resolveServiceDynamically) {
            return true;
        }

        return false;
    }
}
