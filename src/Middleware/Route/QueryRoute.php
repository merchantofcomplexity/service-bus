<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware\Route;

use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Exception\MessageDispatchedFailure;
use React\Promise\Deferred;
use Throwable;

final class QueryRoute extends AbstractRoute
{
    protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope
    {
        $deferred = new Deferred();

        try {
            $messageHandler($envelope->message(), $deferred);
        } catch (Throwable $exception) {
            $deferred->reject(MessageDispatchedFailure::reason($exception));
        } finally {
            $envelope->setPromise($deferred->promise());
        }

        return $envelope;
    }
}
