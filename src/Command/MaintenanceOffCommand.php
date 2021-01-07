<?php

declare(strict_types=1);

namespace Chiron\Maintenance\Command;

use Chiron\Filesystem\Filesystem;
use Chiron\Core\Directories;
use Chiron\Core\Environment;
use Chiron\Core\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Application;
use Chiron\Framework;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Chiron\Bootloader\EnvironmentBootloader;
use Chiron\Maintenance\MaintenanceMode;

use Carbon\Carbon;

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

    public function perform(MaintenanceMode $maintenance): int
    {
        if ($maintenance->isOff()) {
            $this->notice('Application is already live.');

            return self::SUCCESS;
        }

        $details = $maintenance->getDetails();
        $startingTime = Carbon::createFromTimestamp($details['time']);

        try {
            $maintenance->off();
            // estimate the total downtime duration until "now".
            $downTime = $startingTime->diffForHumans(null, true, true, 6);
            $this->info(sprintf('Application is now live! Total downtime: %s.', $downTime));
        } catch (ApplicationException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
