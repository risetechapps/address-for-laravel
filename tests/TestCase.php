<?php

namespace RiseTechApps\Address\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use RiseTechApps\Address\AddressServiceProvider;
use RiseTechApps\Monitoring\MonitoringServiceProvider;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhancedServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AddressServiceProvider::class,
            PostgresqlEnhancedServiceProvider::class,
            MonitoringServiceProvider::class,

        ];
    }

    protected function defineEnvironment($app): void
    {
//        $app['config']->set('database.default', 'testing');
    }
}
