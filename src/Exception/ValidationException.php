<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Exception;

use Illuminate\Contracts\Validation\Validator;

class ValidationException extends RuntimeException
{
    private static Validator $validator;

    public static function withValidator(Validator $validator): self
    {
        self::$validator = $validator;

        $message = 'Validation rules fails:';

        $message .= $validator->errors();

        return new self($message);
    }

    public function getValidator(): Validator
    {
        return self::$validator;
    }
}
