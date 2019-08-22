<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Bus;

use MerchantOfComplexity\Messaging\Contracts\Message;

interface MessageRouteStrategy
{
    public const ASYNC_METADATA_KEY = 'handled-async';

    public const DEFER_ALL_ASYNC = 'defer_all_async';
    public const DEFER_NONE_ASYNC = 'defer_none_async';
    public const DEFER_ONLY_MARKED_ASYNC = 'defer_only_async';

    public function shouldBeDeferred(Message $message): ?Message;

    public function strategyName(): string;
}
