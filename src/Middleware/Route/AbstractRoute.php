<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware\Route;

use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Exception\InvalidServiceBus;
use MerchantOfComplexity\ServiceBus\Exception\MessageCollection;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageRouteStrategy;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Middleware;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Router;
use Throwable;
use function get_class;

abstract class AbstractRoute implements Middleware
{
    private Router $router;
    private MessageRouteStrategy $routeStrategy;
    private bool $isExceptionCollectible = false;
    private array $collectedExceptions = [];

    /**
     * @var callable
     */
    private $callableHandler = null;

    public function __construct(Router $router,
                                MessageRouteStrategy $routeStrategy,
                                callable $callableHandler = null,
                                bool $isExceptionCollectible = false)
    {
        $this->router = $router;
        $this->routeStrategy = $routeStrategy;
        $this->callableHandler = $callableHandler;
        $this->isExceptionCollectible = $isExceptionCollectible;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        if ($wrappedEnvelope = $this->messageShouldBeDeferred($envelope)) {
            return $wrappedEnvelope;
        }

        $envelope = $this->iterateOverMessageHandler($envelope);

        if ($this->collectedExceptions) {
            throw MessageCollection::collected(...$this->collectedExceptions);
        }

        return $next($envelope);
    }

    private function messageShouldBeDeferred(Envelope $envelope): ?Envelope
    {
        $message = $envelope->message();

        if ($message instanceof Message && $asyncMessage = $this->routeStrategy->shouldBeDeferred($message)) {
            $envelope = $envelope->wrap($asyncMessage);

            $envelope->markMessageReceived();

            return $envelope;
        }

        return null;
    }

    private function iterateOverMessageHandler(Envelope $envelope): Envelope
    {
        foreach ($this->router->route($envelope->messageName()) as $messageHandler) {
            if ($messageHandler) {
                $envelope = $this->resolveMessageHandler($envelope, $this->toCallable($messageHandler));
            }

            if (!$this->collectedExceptions) {
                $envelope->markMessageReceived();
            }
        }

        return $envelope;
    }

    /**
     * @param Envelope $envelope
     * @param callable $messageHandler
     * @return Envelope
     * @throws Throwable
     */
    private function resolveMessageHandler(Envelope $envelope, callable $messageHandler): Envelope
    {
        try {
            $envelope = $this->processMessageHandler($envelope, $messageHandler);
        } catch (Throwable $exception) {
            if (!$this->isExceptionCollectible) {
                throw $exception;
            }

            $this->collectedExceptions[] = $exception;
        }

        return $envelope;
    }

    /**
     * @param Envelope $envelope
     * @param callable $messageHandler
     * @return Envelope
     */
    abstract protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope;

    private function toCallable(object $messageHandler): callable
    {
        switch ($messageHandler) {
            case is_callable($messageHandler):
                return $messageHandler;

            case null !== $this->callableHandler:
                return ($this->callableHandler)($messageHandler);

            default:
                throw InvalidServiceBus::invalidTypeCallable(get_class($messageHandler));
        }
    }
}
