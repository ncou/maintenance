<?php

namespace Chiron\Maintenance\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Http;
use Chiron\Maintenance\Middleware\CheckMaintenanceMiddleware;

final class MaintenanceMiddlewareBootloader extends AbstractBootloader
{
    public function boot(Http $http, HttpConfig $config): void
    {
        // add the maintenance middleware in the top position.
        $http->addMiddleware(CheckMaintenanceMiddleware::class, Http::HIGH);
    }
}
