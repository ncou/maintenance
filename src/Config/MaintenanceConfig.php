<?php

declare(strict_types=1);

namespace Chiron\Maintenance\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Chiron\Dispatcher\ConsoleDispatcher;
use Chiron\Dispatcher\SapiDispatcher;
use Chiron\Dispatcher\RrDispatcher;
use Chiron\Config\AbstractInjectableConfig;

use Closure;
use DateTimeInterface;

// Passer les classes de config en "final" !!!!
class MaintenanceConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'maintenance';

    protected function getConfigSchema(): Schema
    {
        // TODO : on ne devrait pas pouvoir stocker de dispatcher dans le fichier app.php, car c'est plutot défini dans core.php. Par contre il mnaque la partie "commands" pour la console !!!!!
        // TODO : virer le otherItem expect mixed !!!!
        return Expect::structure([
            'message'       => Expect::string()->default('Unavailable for scheduled maintenance.'),
            'retry'         => Expect::anyOf(Expect::int(), Expect::interface(DateTimeInterface::class))->nullable()->assert(Closure::fromCallable([$this, 'isRetryValueValid']), 'retry value'),
            'allowed'       => Expect::listOf('string'),
        ]);
    }

    public function getMessage(): string
    {
        return $this->get('message');
    }

    // return null | int | DateTimeInterface object
    public function getRetry()
    {
        return $this->get('retry');
    }

    public function getAllowed(): array
    {
        return $this->get('allowed');
    }

    // $retry could be null or an int (should be > 0) or a DateTimeInterface object.
    private function isRetryValueValid($retry): bool
    {
        if (is_int($retry)) {
            return ($retry > 0);
        }

        // TODO : vérifier que la date+heure qui est passé en paramétre est bien suppérieur à la date du jour ("now").
        // handle null and DateTimeInterface case.
        return true;
    }
}
