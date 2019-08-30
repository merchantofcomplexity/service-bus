<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Events;

use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\Messaging\Contracts\MessageFactory;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexity\Tracker\Contracts\NamedEvent;
use MerchantOfComplexity\Tracker\Contracts\SubscribedEvent;

final class FQCNMessageSubscriber implements SubscribedEvent
{
    public const MESSAGE_NAME_KEY = 'message_name';

    private MessageFactory $messageFactory;

    public function __construct(MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function priority(): int
    {
        return 40000;
    }

    public function subscribeTo(): NamedEvent
    {
        return new DispatchedEvent();
    }

    public function applyTo(): callable
    {
        return function (ActionEvent $event): void {
            $message = $event->message();

            if (is_array($message) && array_key_exists(self::MESSAGE_NAME_KEY, $message)) {
                $convertedMessage = $this->createMessageFromArray($message);

                $event->setMessage($convertedMessage);

                $event->setMessageName($convertedMessage->messageName());
            }
        };
    }

    private function createMessageFromArray(array $message): Message
    {
        $messageName = $message[self::MESSAGE_NAME_KEY];

        unset($message[self::MESSAGE_NAME_KEY]);

        return $this->messageFactory->createMessageFromArray($messageName, $message);
    }
}
