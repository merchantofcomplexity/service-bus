<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Concerns;

use React\Promise\PromiseInterface;
use Throwable;

trait PromiseHandler
{
    /**
     * Handle promise
     *
     * @param PromiseInterface $promise
     * @param bool $raiseException
     * @return mixed
     * @throws Throwable
     */
    public function handlePromise(PromiseInterface $promise, bool $raiseException = true)
    {
        $exception = null;

        $result = null;

        $promise->then(
            function ($data) use (&$result) {
                $result = $data;
            },
            function ($exc) use (&$exception) {
                $exception = $exc;
            }
        );

        if ($raiseException && $exception instanceof Throwable) {
            throw $exception;
        }

        return $exception ?? $result;
    }
}
