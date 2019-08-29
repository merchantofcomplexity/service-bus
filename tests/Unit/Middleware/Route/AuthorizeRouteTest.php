<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\ServiceBus\Unit\Middleware\Route;

use MerchantOfComplexity\ServiceBus\Envelope;
use MerchantOfComplexity\ServiceBus\Middleware\AuthorizeRoute;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\AuthorizationService;
use MerchantOfComplexityTest\ServiceBus\TestCase;

class AuthorizeRouteTest extends TestCase
{
    /**
     * @test
     */
    public function it_authorize_route(): void
    {
        $service = $this->prophesize(AuthorizationService::class);
        $envelope = $this->prophesize(Envelope::class);

        $envelope->message()->willReturn('foo');
        $envelope->messageName()->willReturn('foo');

        $service->isGranted('foo', null)->willReturn(true);

        $middleware = new AuthorizeRoute($service->reveal());

        $response = $middleware->handle($env = $envelope->reveal(), function (Envelope $env) {
            return $env;
        });

        $this->assertEquals($env, $response);
    }

    /**
     * @test
     */
    public function it_authorize_route_by_passing_message_object_as_context(): void
    {
        $service = $this->prophesize(AuthorizationService::class);
        $envelope = $this->prophesize(Envelope::class);

        $message = new \stdClass();
        $envelope->message()->willReturn($message);
        $envelope->messageName()->willReturn('foo');

        $service->isGranted('foo', $message)->willReturn(true);

        $middleware = new AuthorizeRoute($service->reveal());

        $response = $middleware->handle($env = $envelope->reveal(), function (Envelope $env) {
            return $env;
        });

        $this->assertEquals($env, $response);
    }

    /**
     * @test
     * @expectedException \MerchantOfComplexity\ServiceBus\Exception\AuthorizationDenied
     * @expectedExceptionMessage You are not authorized to a access the resource
     */
    public function it_raise_exception_when_message_is_not_authorized(): void
    {
        $service = $this->prophesize(AuthorizationService::class);
        $envelope = $this->prophesize(Envelope::class);

        $envelope->message()->willReturn('foo');
        $envelope->messageName()->willReturn('foo');

        $service->isGranted('foo', null)->willReturn(false);

        $middleware = new AuthorizeRoute($service->reveal());

        $middleware->handle($envelope->reveal(), function (Envelope $env) {});
    }
}
