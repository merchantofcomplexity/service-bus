<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Support\Contracts\Message;

use MerchantOfComplexity\Messaging\Contracts\Message;

interface ValidateMessage extends Message
{
    public function getValidationRules(): array;
}
