<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Message;

use MerchantOfComplexity\Messaging\Contracts\Message;

interface MessageProducer
{
    /**
     * Produce message async
     *
     * @param Message $message
     */
    public function __invoke(Message $message): void;
}
