<?php

namespace Chiron\Maintenance\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Publisher\Publisher;

final class PublishMaintenanceBootloader extends AbstractBootloader
{
    public function boot(Publisher $publisher, Directories $directories): void
    {
        // copy the configuration file template from the package "config" folder to the user "config" folder.
        $puerhable->add(__DIR__ . '/../../config/maintenance.php.dist', $directories->get('@config/maintenance.php'));
    }
}
