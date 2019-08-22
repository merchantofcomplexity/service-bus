<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Middleware;

use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Support\Concerns\DetectMessageName;
use Psr\Log\LoggerInterface;
use Throwable;

final class MessageLogging
{
    use DetectMessageName;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        $context = $this->buildContext($envelope);

        $this->logger->debug("Starting handling message {$context['message_name']}", $context);

        try {
            $envelope = $next($envelope);
        } catch (Throwable $exception) {
            $context['exception'] = $exception;

            $this->logger->warning("An exception occurred while handling message {$context['message_name']}", $context);

            throw $exception;
        }

        $context = $this->buildContext($envelope);

        $this->logger->debug("Finished handling message {$context['message_name']}", $context);

        return $envelope;
    }

    protected function buildContext(Envelope $envelope): array
    {
        $message = $envelope->message();

        $messageName = $this->detectMessageName($message);

        if (is_object($message) && method_exists($message, 'toArray')) {
            $message = $message->toArray();
        }

        return ['message' => $message, 'message_name' => $messageName];
    }
}
