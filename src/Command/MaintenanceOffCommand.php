<?php

declare(strict_types=1);

namespace Chiron\Maintenance\Command;

use Chiron\Filesystem\Filesystem;
use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Console\AbstractCommand;
use Chiron\Encrypter\Config\EncrypterConfig;
use Chiron\Support\Security;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Application;
use Chiron\Framework;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Chiron\Bootloader\EnvironmentBootloader;

use Chiron\Boot\Path;

use Chiron\Maintenance\MaintenanceMode;


//https://github.com/awjudd/maintenance-mode/blob/master/src/MisterPhilip/MaintenanceMode/Console/Commands/EndMaintenanceCommand.php#L22

// TODO : renommer en site:up et site:down ou maintenance:on / maintenance:off ou up / down

/**
 * A console command to display information about the current installation.
 */
final class MaintenanceOffCommand extends AbstractCommand
{
    protected static $defaultName = 'maintenance:off';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Bring the application out of maintenance mode');
    }

    //https://github.com/awjudd/maintenance-mode/blob/master/src/MisterPhilip/MaintenanceMode/Console/Commands/EndMaintenanceCommand.php#L22
    //https://github.com/laravel/framework/blob/0b12ef19623c40e22eff91a4b48cb13b3b415b25/src/Illuminate/Foundation/Console/DownCommand.php#L38
    public function perform(MaintenanceMode $maintenance): int
    {
        // TODO : faire un try/catch autour de cet appel ? et afficher un $this->error() + retunr ERROR_CODE ????
        $maintenance->off();

        $this->info('Application is now live.');

        return self::SUCCESS;
    }
}
