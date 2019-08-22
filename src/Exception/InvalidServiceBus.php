<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Exception;

class InvalidServiceBus extends RuntimeException
{
    public static function invalidMessageHandlerType(string $messageName, string $messageType): self
    {
        $message = "Message handler for message name $messageName ";
        $message .= "must be a string, an object or a callable ";
        $message .= "but get type $messageType";

        return new self($message);
    }

    public static function invalidTypeCallable(?string $messageHandler): self
    {
        return new self("Message handler must be a callable: {$messageHandler}");
    }

    public static function nullMessageHandlerNotAllowed(string $messageName): self
    {
        return new self("Null message handler for message name {$messageName} is not allowed");
    }

    public static function tooManyHandlers(string $messageName, string $routerHandler): self
    {
        return new self("Router handler {$routerHandler} can route to one handler only for message name {$messageName}");
    }

    public static function messageAlreadyProducedAsync(string $messageName): self
    {
        return new self("Message name {$messageName} has been already produced async");
    }

    public static function missingCallableMethodName(string $messageHandler, string $methodName): self
    {
        return new self("Method name $methodName missing from message handler $messageHandler");
    }

    public static function invalidContainer(string $messageHandler, string $messageName): self
    {
        $message = "No service locator has been set for message handler {$messageHandler}";
        $message .= " and message name {$messageName}";

        return new self($message);
    }
}
