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

// TODO : ajouter un header pour virer le cache : 'Cache-Control' = 'no-cache, must-revalidate';
//https://github.com/shopsys/production/blob/master/app/maintenance.php

// TODO : fonction de django:    https://docs.djangoproject.com/fr/3.1/ref/utils/#django.utils.cache.add_never_cache_headers
// Cache-Control: max-age=0, no-cache, no-store, must-revalidate, private
// TODO : attention dans django il y a aussi l'ajout d'un header "Expires" calculé via le code suivant : response.headers['Expires'] = http_date(time.time())
// Mais on pourrait ajouter directement un truc du genre : "Expires" => "Fri, 01 Jan 1990 00:00:00 GMT",

// TODO : créer un middleware pour cacher la réponse : https://itnext.io/laravel-the-hidden-setcacheheaders-middleware-4cd594ba462f

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
        // TODO : faire plutot un if(! ->isOn) dans ce cas on fait le return. et en dessous on throw l'exception.
        if ($this->maintenance->isOn() === true) {
            $details = $this->maintenance->getDetails();

            throw new MaintenanceModeException(
                $details['message'],
                $details['time'],
                $details['retry_after']
            );
        }

        return $handler->handle($request);
    }
}
