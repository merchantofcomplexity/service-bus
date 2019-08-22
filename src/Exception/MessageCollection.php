<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Exception;

use Throwable;

class MessageCollection extends MessageDispatchedFailure
{
    private array $exceptionCollection;

    public static function collected(Throwable ...$exceptions): self
    {
        $messages = '';

        foreach ($exceptions as $exception) {
            $messages .= $exception->getMessage() . "\n";
        }

        $exceptionMessage = "At least one event listener caused an exception.";
        $exceptionMessage .= "Check listener exceptions for details:\n$messages";

        $self = new self($exceptionMessage);

        $self->exceptionCollection = $exceptions;

        return $self;
    }

    /**
     * @return Throwable[]
     */
    public function listenerExceptions(): array
    {
        return $this->exceptionCollection;
    }
}
