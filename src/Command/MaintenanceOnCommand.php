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

use Chiron\Exception\ApplicationException;




//https://github.com/awjudd/maintenance-mode/blob/master/src/MisterPhilip/MaintenanceMode/Console/Commands/StartMaintenanceCommand.php

// TODO : renommer en site:up et site:down ou maintenance:on / maintenance:off ou up / down

/**
 * A console command to display information about the current installation.
 */
final class MaintenanceOnCommand extends AbstractCommand
{
    protected static $defaultName = 'maintenance:on';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Put the application into maintenance mode');
    }

    //https://github.com/laravel/framework/blob/0b12ef19623c40e22eff91a4b48cb13b3b415b25/src/Illuminate/Foundation/Console/UpCommand.php#L29
    public function perform(MaintenanceMode $maintenance): int
    {
        if ($maintenance->isOn()) {
            $this->notice('Application is already down.');

            return self::SUCCESS;
        }

        try {
            $maintenance->on();
            $this->warning('Application is now in maintenance mode.');
        } catch (ApplicationException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
