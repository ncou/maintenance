<?php

namespace Chiron\Maintenance\Bootloader;

use Chiron\Boot\Directories;
use Chiron\Bootload\AbstractBootloader;
use Chiron\PublishableCollection;
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
