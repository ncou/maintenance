<?php

namespace Chiron\Maintenance\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Http\Http;
use Chiron\Maintenance\Middleware\CheckMaintenanceMiddleware;

final class MaintenanceMiddlewareBootloader extends AbstractBootloader
{
    public function boot(Http $http): void
    {
        // add the maintenance middleware in the top position.
        $http->addMiddleware(CheckMaintenanceMiddleware::class, Http::PRIORITY_HIGH);
    }
}
