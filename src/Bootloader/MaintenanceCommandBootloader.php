<?php

namespace Chiron\Maintenance\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Console\Console;
use Chiron\Maintenance\Command\MaintenanceOnCommand;
use Chiron\Maintenance\Command\MaintenanceOffCommand;

final class MaintenanceCommandBootloader extends AbstractBootloader
{
    public function boot(Console $console): void
    {
        $console->addCommand(MaintenanceOnCommand::getDefaultName(), MaintenanceOnCommand::class);
        $console->addCommand(MaintenanceOffCommand::getDefaultName(), MaintenanceOffCommand::class);
    }
}
