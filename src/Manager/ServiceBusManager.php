<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus\Manager;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use MerchantOfComplexity\Messaging\Contracts\MessageConverter;
use MerchantOfComplexity\ServiceBus\Async\IlluminateMessageProducer;
use MerchantOfComplexity\ServiceBus\CommandBus;
use MerchantOfComplexity\ServiceBus\EventBus;
use MerchantOfComplexity\ServiceBus\Middleware\Route\CommandRoute;
use MerchantOfComplexity\ServiceBus\Middleware\Route\EventRoute;
use MerchantOfComplexity\ServiceBus\Middleware\Route\Handler\CallableHandler;
use MerchantOfComplexity\ServiceBus\Middleware\Route\QueryRoute;
use MerchantOfComplexity\ServiceBus\Middleware\Route\Strategy\DeferAllAsync;
use MerchantOfComplexity\ServiceBus\Middleware\Route\Strategy\DeferNoneAsync;
use MerchantOfComplexity\ServiceBus\Middleware\Route\Strategy\DeferOnlyMarkedAsync;
use MerchantOfComplexity\ServiceBus\QueryBus;
use MerchantOfComplexity\ServiceBus\Router\CommandRouter;
use MerchantOfComplexity\ServiceBus\Router\EventRouter;
use MerchantOfComplexity\ServiceBus\Router\MultipleHandlerRouter;
use MerchantOfComplexity\ServiceBus\Router\QueryRouter;
use MerchantOfComplexity\ServiceBus\Router\SingleHandlerRouter;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Messager;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\MessageRouteStrategy;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Middleware;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Router;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Manager\ServiceBusManager as BaseServiceBusManager;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Message\MessageProducer;
use MerchantOfComplexity\Tracker\Contracts\Tracker;
use RuntimeException;

abstract class ServiceBusManager implements BaseServiceBusManager
{
    protected array $buses = [];
    private string $namespace = 'moc';
    private Application $app;
    private array $config;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function make(string $name, string $type): Messager
    {
        $busKey = $this->determineBusKey($name, $type);

        if (isset($this->buses[$busKey])) {
            return $this->buses[$busKey];
        }

        return $this->buses[$busKey] = $this->create($name, $type);
    }

    protected function create(string $name, string $type): Messager
    {
        $typeKey = $this->determineBusType($type);

        $busConfig = $this->fromConfig("buses.$typeKey.$name");

        if (empty($busConfig)) {
            throw new RuntimeException("Service bus configuration is empty");
        }

        $serviceBusClass = $busConfig['service_bus'] ?? null;

        if (!$serviceBusClass || !class_exists($serviceBusClass)) {
            $message = "Invalid service bus class for bus name $name and bus $type ";
            $message .= "Service bus must be configured as fqcn class and implement contract " . Messager::class;

            throw new InvalidArgumentException($message);
        }

        return new $serviceBusClass(
            $this->buildMiddleware($name, $type, $busConfig),
            $this->registerTracker($busConfig)
        );
    }

    protected function buildMiddleware(string $name, string $type, array $busConfig): array
    {
        $middleware = $this->fromConfig("middleware", []);

        $middleware = array_merge($middleware, $busConfig['middleware'] ?? []);

        $middleware [] = [$this->buildRoutingMiddleware($name, $type, $busConfig), 0];

        return $this->sortResolvedMiddlewareByPriority($middleware);
    }

    protected function buildRoutingMiddleware(string $name, $type, $busConfig): Middleware
    {
        $defaultRoute = $busConfig['route_middleware'] ?? null;

        if ($this->app->bound($defaultRoute)) {
            return $this->app->make($defaultRoute);
        }

        $defaultAppRoute = [CommandRoute::class, QueryRoute::class, EventRoute::class];

        if (!in_array($defaultRoute, $defaultAppRoute)) {
            $message = "Invalid route middleware for bus name $name and bus type $type ";
            $message .= "Either register in ioc your own route middleware or your service ";
            $message .= "must be a member of " . (implode(', ', $defaultAppRoute));

            throw new InvalidArgumentException($message);
        }

        $router = $this->newBusRouterInstance($type, $busConfig);
        $strategy = $this->determineRouteStrategy($name, $type, $busConfig);

        $toCallableKey = 'message.handler.to_callable';
        $handlerToCallable = Arr::get($busConfig, $toCallableKey, $this->fromConfig($toCallableKey, false));

        $toCallable = $this->determineHandlerMethod($handlerToCallable);

        $exceptionCollectible = 'message.is_exception_collectible';
        $isExceptionCollectible = Arr::get($busConfig, $exceptionCollectible, $this->fromConfig($exceptionCollectible, false));

        return new $defaultRoute($router, $strategy, $toCallable, $isExceptionCollectible);
    }

    protected function determineHandlerMethod($handlerToCallable): ?callable
    {
        if (!$handlerToCallable) {
            return null;
        }

        if (!is_string($handlerToCallable)) {
            throw new InvalidArgumentException("to callable key must be a string service or method name");
        }

        if ($this->app->bound($handlerToCallable)) {
            return $this->app->make($handlerToCallable);
        }

        return new CallableHandler($handlerToCallable);
    }

    protected function newBusRouterInstance(string $type, array $busConfig): Router
    {
        $nullHandlerKey = 'message.handler.allow_null';
        $allowNullHandler = Arr::get($busConfig, $nullHandlerKey, $this->fromConfig($nullHandlerKey, false));

        $resolverKey = 'message.handler.resolver';
        $container = Arr::get($busConfig, $resolverKey, $this->fromConfig($resolverKey, null));

        $eventsHandlerKey = $resolverKey = 'message.handler.events';
        $eventsHandler = Arr::get($busConfig, $eventsHandlerKey, $this->fromConfig($eventsHandlerKey, []));

        $map = $busConfig['map'];

        if (!$map || empty($map)) {
            throw new InvalidArgumentException("Service bus routing map key is missing or empty");
        }

        if (EventBus::class === $type && !empty($eventsHandler)) {
            $map = $this->addEventsHandlerToEventMap($busConfig['map'], $eventsHandler);
        }

        return $this->determineBusRouter($type, $map, $allowNullHandler, $container);
    }

    protected function determineRouteStrategy(string $name, string $type, array $busConfig): MessageRouteStrategy
    {
        $strategyKey = 'message.route_strategy';
        $routeStrategy = Arr::get($busConfig, $strategyKey, $this->fromConfig($strategyKey, null));

        if (!is_string($routeStrategy)) {
            throw new InvalidArgumentException("Route strategy missing or invalid for bus name $name and type $type");
        }

        if ($this->app->bound($routeStrategy)) {
            return $this->app->make($routeStrategy);
        }

        if ($routeStrategy === MessageRouteStrategy::DEFER_NONE_ASYNC) {
            return new DeferNoneAsync();
        }

        $producer = $this->determineMessageProducer($busConfig);

        switch ($routeStrategy) {
            case MessageRouteStrategy::DEFER_ALL_ASYNC:
                return new DeferAllAsync($producer);
            case MessageRouteStrategy::DEFER_ONLY_MARKED_ASYNC:
                return new DeferOnlyMarkedAsync($producer);
        }

        throw new InvalidArgumentException("Unable to load route strategy for bus name $name and type $type");
    }

    protected function determineMessageProducer(array $busConfig): MessageProducer
    {
        $producerKey = 'message.producer.service';
        $producer = Arr::get($busConfig, $producerKey, $this->fromConfig($producerKey, null));

        if (!is_string($producer)) {
            throw new InvalidArgumentException("Message producer is mandatory and must be a string");
        }

        if ($this->app->bound($producer)) {
            return $this->app->make($producer);
        }

        $connectionKey = 'message.producer.connection';
        $connection = Arr::get($busConfig, $connectionKey, $this->fromConfig($connectionKey, null));

        $queueKey = 'message.producer.queue';
        $queue = Arr::get($busConfig, $queueKey, $this->fromConfig($queueKey, null));

        return $this->buildMessageProducerInstance($producer, $connection, $queue, $busConfig);
    }

    protected function buildMessageProducerInstance(string $messageProducer,
                                                    ?string $connection,
                                                    ?string $queue,
                                                    array $busConfig): MessageProducer
    {
        if ($messageProducer !== IlluminateMessageProducer::class) {
            $message = "Invalid message producer $messageProducer ";
            $message .= "if you need specific message producer register your own service in ioc ";
            $message .= "and set it up in your bus configuration";

            throw new InvalidArgumentException($message);
        }

        $busClass = $busConfig['service_bus'];

        return new IlluminateMessageProducer(
            $this->app->make(QueueingDispatcher::class),
            $this->app->make(MessageConverter::class),
            $busClass,
            $connection,
            $queue
        );
    }

    protected function determineBusRouter(string $type, array $map, bool $allowNullHandler, ?string $container = null): Router
    {
        if ($container) {
            $container = $this->app->make($container);
        }

        switch ($this->determineBusType($type)) {
            case 'command':
                return new CommandRouter(
                    new SingleHandlerRouter($map, $container, $allowNullHandler)
                );
            case 'query':
                return new QueryRouter(
                    new SingleHandlerRouter($map, $container, $allowNullHandler)
                );
            case 'event':
                return new EventRouter(
                    new MultipleHandlerRouter($map, $container, $allowNullHandler)
                );
        }

        throw new RuntimeException("Unable to load bus router instance");
    }

    public function registerTracker(array $busConfig): Tracker
    {
        $tracker = $this->fromConfig("tracker.service");

        if ($busTracker = Arr::get($busConfig, "tracker.service")) {
            $tracker = $busTracker;
        }

        $events = array_merge(
            $this->fromConfig("tracker.events.named"),
            $this->fromConfig("tracker.events.subscribers")
        );

        if ($busEvents = Arr::get($busConfig, "tracker.events", [])) {
            $events = array_merge($events, Arr::flatten($busEvents));
        }

        /** @var Tracker $tracker */
        $tracker = clone $this->app->make($tracker);

        foreach ($events as $event) {
            $tracker->subscribe(
                $this->app->make($event)
            );
        }

        return $tracker;
    }

    protected function sortResolvedMiddlewareByPriority(array $middleware): array
    {
        return (new Collection($middleware))
            ->sortByDesc(function (array $stack): int {
                [, $priority] = $stack;

                return $priority;
            })
            ->transform(function (array $stack): Middleware {
                [$middleware] = $stack;

                if (is_string($middleware)) {
                    return $this->app->make($middleware);
                }

                return $middleware;
            })->values()->toArray();
    }

    /**
     * Add global event handlers to an event map
     *
     * @param array $map
     * @param array $events
     * @return array
     */
    protected function addEventsHandlerToEventMap(array $map, array $events = [])
    {
        if (empty($events)) {
            return $map;
        }

        foreach ($map as $eventName => &$eventHandler) {
            if (is_string($eventHandler)) {
                $eventHandler = [$eventHandler];
            }

            $eventHandler = array_merge($eventHandler, $events);
        }

        return $map;
    }

    protected function determineBusType(string $busClass): string
    {
        switch ($busClass) {
            case CommandBus::class:
                return 'command';
            case EventBus::class:
                return 'event';
            case QueryBus::class:
                return 'query';
        }

        throw new RuntimeException("Invalid bus type $busClass");
    }

    protected function determineBusKey(string $busName, string $busType): string
    {
        $type = $this->determineBusType($busType);

        return mb_strtolower(sprintf('%s:%s.%s', $this->namespace, $type, $busName));
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function fromConfig(string $key, $default = null)
    {
        return Arr::get($this->config, "{$this->namespace}.$key", $default);
    }
}
