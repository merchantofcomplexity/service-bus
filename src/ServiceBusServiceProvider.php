<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use MerchantOfComplexity\ServiceBus\Manager\DefaultBusManager;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Manager\ServiceBusManager;

class ServiceBusServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$this->getConfigPath() => config_path('service_bus.php')],
                'config'
            );
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'service_bus');

        $this->app->singleton(ServiceBusManager::class, function (Application $app): ServiceBusManager {
            return new DefaultBusManager($app, $app->get('config')->get('service_bus'));
        });
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../config/service_bus.php';
    }
}
