<?php

namespace Chiron\Maintenance\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Http\MiddlewareQueue;
use Chiron\Maintenance\Middleware\CheckMaintenanceMiddleware;

final class MaintenanceMiddlewareBootloader extends AbstractBootloader
{
    public function boot(MiddlewareQueue $middlewares): void
    {
        // add the maintenance middleware in the top position.
        $middlewares->addMiddleware(CheckMaintenanceMiddleware::class, MiddlewareQueue::PRIORITY_HIGH);
    }
}
