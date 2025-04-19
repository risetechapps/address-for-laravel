<?php

namespace RiseTechApps\Address;

use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateBillingEvent;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDefaultEvent;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDeliveryEvent;

class AddressServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Event::listen(AddressCreateOrUpdateDefaultEvent::class, Listeners\AddressCreateOrUpdateDefaultListener::class);
        Event::listen(AddressCreateOrUpdateDeliveryEvent::class, Listeners\AddressCreateOrUpdateDeliveryListener::class);
        Event::listen(AddressCreateOrUpdateBillingEvent::class, Listeners\AddressCreateOrUpdateBillingListener::class);

        $this->registerMacros();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Register the main class to use with the facade
         $this->app->singleton(Address::class, function () {
            return new Address;
        });
    }

    protected function registerMacros(): void
    {

        if(!ResponseFactory::hasMacro('jsonSuccess')){
            ResponseFactory::macro('jsonSuccess', function ($data = []) {
                $response = ['success' => true];
                if (!empty($data)) $response['data'] = $data;
                return response()->json($response);
            });
        }

        if(!ResponseFactory::hasMacro('jsonError')){
            ResponseFactory::macro('jsonError', function ($data = null) {
                $response = ['success' => false];
                if (!is_null($data)) $response['message'] = $data;
                return response()->json($response, 412);
            });
        }

        if(!ResponseFactory::hasMacro('jsonGone')) {
            ResponseFactory::macro('jsonGone', function ($data = null) {
                $response = ['success' => false];
                if (!is_null($data)) $response['message'] = $data;
                return response()->json($response, 410);
            });
        }

        if(!ResponseFactory::hasMacro('jsonNotValidated')) {
            ResponseFactory::macro('jsonNotValidated', function ($message = null, $error = null) {
                $response = ['success' => false];
                if (!is_null($message)) $response['message'] = $message;

                return response()->json($response, 422);
            });
        }
    }
}
