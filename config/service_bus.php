<?php

use MerchantOfComplexity\ServiceBus\Async\IlluminateMessageProducer;
use MerchantOfComplexity\ServiceBus\CommandBus;
use MerchantOfComplexity\ServiceBus\EventBus;
use MerchantOfComplexity\ServiceBus\Middleware\MessageLogging;
use MerchantOfComplexity\ServiceBus\Middleware\MessageTracker;
use MerchantOfComplexity\ServiceBus\Middleware\QueryContent;
use MerchantOfComplexity\ServiceBus\Middleware\Route\CommandRoute;
use MerchantOfComplexity\ServiceBus\Middleware\Route\EventRoute;
use MerchantOfComplexity\ServiceBus\Middleware\Route\QueryRoute;
use MerchantOfComplexity\ServiceBus\QueryBus;
use MerchantOfComplexity\ServiceBus\Support\Container\IlluminateContainer;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageRouteStrategy;
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

        /*
       |--------------------------------------------------------------------------
       | Message
       |--------------------------------------------------------------------------
       |
       | Default message configuration for each bus
       | each key an be overridden under his own bus
       |
       */
        'message' => [

            /**
             * Message producer
             *
             * Dispatch message async according to a strategy
             */
            'producer' => [
                'service' => IlluminateMessageProducer::class,
                'connection' => null,
                'queue' => null,
            ],

            /**
             * Route strategy
             *
             * available defer_all_async, defer_none_async, defer_only_marked_async
             * @see MessageRouteStrategy
             *
             */
            'route_strategy' => 'defer_only_marked_async',

            /**
             * Message handler
             *
             */
            'handler' => [

                /**
                 * Allow null message handler
                 *
                 * only even bus should be allowed null message handler
                 *
                 */
                'allow_null' => false,

                /**
                 * Message handler to callable
                 *
                 * naming your invokable handler
                 *
                 */
                'to_callable' => false,

                /**
                 * Message string resolver
                 *
                 */
                'resolver' => IlluminateContainer::class,

                /**
                 * Message event handlers
                 *
                 * push global event handlers for all buses
                 *
                 */
                'events' => []
            ],

            /**
             * Collect exception raise from message handlers
             *
             * should be reserved for event bus
             *
             */
            'is_exception_collectible' => false
        ],

        /*
        |--------------------------------------------------------------------------
        | Tracker
        |--------------------------------------------------------------------------
        |
        */
        'tracker' => [
            'service' => DefaultTracker::class,

            'events' => [
                'named' => [
                    DispatchedEvent::class,
                    FinalizedEvent::class,
                ],

                'subscribers' => [
                    DetectMessageNameSubscriber::class,
                    FQCNMessageSubscriber::class,
                    InitializedSubscriber::class,
                    MessageValidatorSubscriber::class,
                    ExceptionSubscriber::class,
                ]
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Middleware
        |--------------------------------------------------------------------------
        |
        */
        'middleware' => [
            [QueryContent::class, 20],
            [MessageLogging::class, 19],
            [MessageTracker::class, 10]
        ],

        /*
        |--------------------------------------------------------------------------
        | Bus configuration
        |--------------------------------------------------------------------------
        |
        */
        'buses' => [

            'command' => [

                'default' => [
                    'service_bus' => CommandBus::class,
                    'route_middleware' => CommandRoute::class,
                    'map' => []
                ],

            ],

            'event' => [

                'default' => [
                    'service_bus' => EventBus::class,
                    'route_middleware' => EventRoute::class,
                    'message' => [
                        'handler' => [
                            'allow_null' => true,
                            'to_callable' => 'onEvent'
                        ],
                        'is_exception_collectible' => true

                    ],
                    'map' => [],
                ],

            ],

            'query' => [
                'default' => [
                    'service_bus' => QueryBus::class,
                    'route_middleware' => QueryRoute::class,
                    'message' => [
                        'handler' => [
                            'to_callable' => 'query'
                        ]
                    ],
                    'map' => [],
                ],
            ],
        ]
    ]
];
