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
use Chiron\Config\Helper\Validator;

final class MaintenanceConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'maintenance';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'storage_path'  => Expect::string()->default(directory('@runtime/framework/down.json')),
            'maintenance' => Expect::structure([
                'message'     => Expect::string()->default('Unavailable for scheduled maintenance.'),
                'retry_after' => Expect::int()->nullable(),
                'allowed_ip'  => Expect::listOf('string')->assert(Closure::fromCallable([$this, 'isValidIpsAdresses']), 'IP format'),
            ])
        ]);
    }

    public function getStoragePath(): string
    {
        return $this->get('storage_path');
    }

    public function getMessage(): string
    {
        return $this->get('maintenance.message');
    }

    public function getRetryAfter(): ?int
    {
        return $this->get('maintenance.retry_after');
    }

    public function getAllowedIp(): array
    {
        return $this->get('maintenance.allowed_ip');
    }

    private function isValidIpsAdresses(array $ipAdresses): bool
    {
        // TODO : utiliser plutot ce bout de code. éventuellement ajouter le flag : FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 en 3eme paramétre.
        //return (bool) filter_var($ip, FILTER_VALIDATE_IP);

        foreach ($ipAdresses as $ip) {
            if (! Validator::isIp($ip)) {
                return false;
            }
        }

        return true;
    }
}
