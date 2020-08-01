<?php

declare(strict_types=1);

namespace Chiron\Maintenance\Middleware;

use Chiron\Http\Exception\Server\ServiceUnavailableHttpException;
use DateTimeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\MaintenanceMode;

class CheckMaintenanceMiddleware implements MiddlewareInterface
{
    /** @var MaintenanceMode */
    private $maintenance;

// TODO : faire une classe de Facade pour la partie Maintenance
    public function __construct(MaintenanceMode $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->maintenance->isDown()) {
            $data = $this->maintenance->getDownInformation();

            // TODO : créer une exception dédiée avec en paramétre l'heure à laquelle le site est passé à down ($data['time']) et calculer dans l'exception en ajoutant le time + le délais du retry en secondes quand c'est qu'il sera de retour en ligne (avec la propriété public dans l'exception "willBeAvailableAt"). Exemple : https://github.com/laravel/framework/blob/5.5/src/Illuminate/Foundation/Http/Exceptions/MaintenanceModeException.php
            // TODO : modifier l'exception pour qu'elle fasse une vérification que la valeur retry est un int supérieur à 0 ou une string avec une regex pour valider le format de la date !!!! => utiliser la rexex suivante : https://github.com/sabre-io/http/blob/master/lib/functions.php#L34

            throw new ServiceUnavailableHttpException($data['retry'], $data['message']);
        }

        return $handler->handle($request);
    }
}
