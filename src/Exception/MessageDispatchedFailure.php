<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Exception;

use Throwable;

class MessageDispatchedFailure extends RuntimeException
{
    public static function reason(Throwable $dispatchException): MessageDispatchedFailure
    {
        return new static(
            'Message dispatch failed. See previous exception for details.',
            422,
            $dispatchException
        );
    }
}
