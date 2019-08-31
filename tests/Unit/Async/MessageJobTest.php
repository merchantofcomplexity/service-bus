<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Async;

use MerchantOfComplexity\ServiceBus\Async\MessageJob;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Messager;
use MerchantOfComplexityTest\ServiceBus\TestCase;
use Psr\Container\ContainerInterface;

class MessageJobTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_message(): void
    {
        $this->markTestSkipped('Message job todo');
    }
}
