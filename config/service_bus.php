<?php

use MerchantOfComplexity\Messaging\Factory\NoOpMessageConverter;
use MerchantOfComplexity\ServiceBus\CommandBus;
use MerchantOfComplexity\ServiceBus\EventBus;
use MerchantOfComplexity\ServiceBus\Middleware\MessageLogging;
use MerchantOfComplexity\ServiceBus\Middleware\MessageTracker;
use MerchantOfComplexity\ServiceBus\Middleware\QueryContent;
use MerchantOfComplexity\ServiceBus\Middleware\Route\CommandRoute;
use MerchantOfComplexity\ServiceBus\Middleware\Route\EventRoute;
use MerchantOfComplexity\ServiceBus\Middleware\Route\QueryRoute;
use MerchantOfComplexity\ServiceBus\QueryBus;
use MerchantOfComplexity\ServiceBus\Router\CommandRouter;
use MerchantOfComplexity\ServiceBus\Router\EventRouter;
use MerchantOfComplexity\ServiceBus\Router\QueryRouter;
use MerchantOfComplexity\ServiceBus\Support\Container\IlluminateContainer;
use MerchantOfComplexity\ServiceBus\Support\Events\DetectMessageNameSubscriber;
use MerchantOfComplexity\ServiceBus\Support\Events\DispatchedEvent;
use MerchantOfComplexity\ServiceBus\Support\Events\ExceptionSubscriber;
use MerchantOfComplexity\ServiceBus\Support\Events\FinalizedEvent;
use MerchantOfComplexity\ServiceBus\Support\Events\FQCNMessageSubscriber;
use MerchantOfComplexity\ServiceBus\Support\Events\InitializedSubscriber;
use MerchantOfComplexity\ServiceBus\Support\Events\MessageValidatorSubscriber;
use MerchantOfComplexity\Tracker\DefaultTracker;

return [

    'moc' => [

        'bus' => [

            'message' => [
                'converter' => NoOpMessageConverter::class,

                'producer' => '',

                'route_strategy' => 'defer_only_marked_async',

                'handler' => [
                    'allow_null' => false,
                    'to_callable' => false,
                    'resolver' => IlluminateContainer::class
                ]
            ],

            'tracker' => [
                'service' => DefaultTracker::class,

                'events' => [
                    'named' => [
                        DispatchedEvent::class,
                        FinalizedEvent::class,
                    ],

                    'subscribers' => [
                        DetectMessageNameSubscriber::class,
                        ExceptionSubscriber::class,
                        FQCNMessageSubscriber::class,
                        InitializedSubscriber::class,
                        MessageValidatorSubscriber::class
                    ]
                ]
            ],
        ],

        'middleware' => [
            [QueryContent::class, 20],
            [MessageLogging::class, 19],
            [MessageTracker::class, 10]
        ],

        'buses' => [

            'command' => [
                'service_bus' => CommandBus::class,
                'middleware' => [],
                'route' => CommandRoute::class,
                'router' => CommandRouter::class,
                'routes' => [

                ]
            ],

            'event' => [
                'service_bus' => EventBus::class,
                'middleware' => [],
                'route' => EventRoute::class,
                'router' => EventRouter::class,
                'message' => [
                    'handler' => [
                        'allow_null' => true,
                        'to_callable' => 'onEvent'
                    ]
                ],
                'routes' => [

                ]
            ],

            'query' => [
                'service_bus' => QueryBus::class,
                'middleware' => [],
                'route' => QueryRoute::class,
                'router' => QueryRouter::class,
                'message' => [
                    'handler' => [
                        'allow_null' => true,
                        'to_callable' => 'query'
                    ]
                ],
                'routes' => [

                ]
            ],
        ]
    ]
];
