<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Container;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFound extends \RuntimeException implements NotFoundExceptionInterface
{
}
