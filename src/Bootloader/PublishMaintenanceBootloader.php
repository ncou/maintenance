<?php

namespace Chiron\Maintenance\Bootloader;

use Chiron\Boot\Directories;
use Chiron\Bootload\AbstractBootloader;
use Chiron\PublishableCollection;

final class PublishMaintenanceBootloader extends AbstractBootloader
{
    public function boot(PublishableCollection $publishable, Directories $directories): void
    {
        // copy the configuration file template from the package "config" folder to the user "config" folder.
        $publishable->add(__DIR__ . '/../../config/maintenance.php.dist', $directories->get('@config/maintenance.php'));
    }
}
