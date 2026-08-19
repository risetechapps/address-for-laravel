<?php

namespace RiseTechApps\Address;

use Illuminate\Support\ServiceProvider;
use RiseTechApps\Address\Models\Address;
use RiseTechApps\Address\Observers\AddressObserver;

class AddressServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('address.php'),
            ], ['config', 'address-config']);
        }

        Address::observe(AddressObserver::class);
    }

    /**
     * Register the application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'address');

        // Register the Address model to use with the facade
        $this->app->singleton('address', fn() => new Models\Address);
    }
}
