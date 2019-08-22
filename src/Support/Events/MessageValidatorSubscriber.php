<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Events;

use Illuminate\Contracts\Validation\Factory;
use MerchantOfComplexity\ServiceBus\Exception\ValidationException;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Message\PreValidateMessage;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Message\ValidateMessage;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexity\Tracker\Contracts\NamedEvent;
use MerchantOfComplexity\Tracker\Contracts\SubscribedEvent;
use Throwable;

final class MessageValidatorSubscriber implements SubscribedEvent
{
    private Factory $validationFactory;

    public function __construct(Factory $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    public function priority(): int
    {
        return 30000;
    }

    public function subscribeTo(): NamedEvent
    {
        return new DispatchedEvent();
    }

    public function applyTo(): callable
    {
        return function (ActionEvent $event): void {
            $message = $event->message();

            if (!$message instanceof ValidateMessage) {
                return;
            }

            try {
                $this->validateMessage($message);
            } catch (Throwable $exception) {
                if ($message instanceof PreValidateMessage) {
                    throw $exception;
                }

                $event->setException($exception);
            }
        };
    }

    private function validateMessage(ValidateMessage $message)
    {
        $validator = $this->validationFactory->make($message->payload(), $message->getValidationRules());

        if ($validator->fails()) {
            throw ValidationException::withValidator($validator);
        }
    }
}
