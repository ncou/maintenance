<?php

declare(strict_types=1);

namespace Chiron\Maintenance\Middleware;

use Chiron\Maintenance\Exception\MaintenanceModeException;
use DateTimeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Maintenance\MaintenanceMode;

final class CheckMaintenanceMiddleware implements MiddlewareInterface
{
    /** @var MaintenanceMode */
    private $maintenance;

    public function __construct(MaintenanceMode $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //@todo : add the ip adresses filtering in this middleware (cf data "allowed_ip" in the details array).
        if ($this->maintenance->isOn()) {
            $details = $this->maintenance->getDetails();

            throw new MaintenanceModeException($details['message'], $details['time'], $details['retry_after']);
        }

        return $handler->handle($request);
    }
}
