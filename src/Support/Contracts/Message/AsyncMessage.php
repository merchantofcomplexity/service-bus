<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Message;

use MerchantOfComplexity\Messaging\Contracts\Message;

interface AsyncMessage extends Message
{
    /**
     * Async Message
     *
     * Message contract to dispatch message async
     * work conjointly with route message strategy "defer_only_marked_async"
     *
     */
}
