<?php

declare(strict_types=1);

namespace Chiron\Maintenance;

use Chiron\Bootload\BootloaderInterface;
use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Dispatcher\DispatcherInterface;
use Chiron\ErrorHandler\RegisterErrorHandler;
use Chiron\Exception\ApplicationException;
use Chiron\Container\Container;

use Chiron\Bootloader\SettingsBootloader;
use Chiron\Bootloader\EnvironmentBootloader;
use Chiron\Bootloader\ConfigureBootloader;
use Chiron\Bootloader\DirectoriesBootloader;
use Chiron\Bootloader\PackageManifestBootloader;
use Chiron\Bootloader\MutationsBootloader;
use Chiron\Bootloader\PublishableCollectionBootloader;
use Chiron\Config\InjectableConfigInterface;
use Chiron\Config\InjectableConfigMutation;
use Chiron\Provider\ConfigureServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\LoggerServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\RoadRunnerServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;
use Chiron\Container\SingletonInterface;

use Chiron\Filesystem\Exception\FilesystemException;

use Chiron\Maintenance\Config\MaintenanceConfig;
use Chiron\Filesystem\Filesystem;
use Carbon\Carbon;

// TODO : utiliser cet exemple pour la gestion des IP :
//*****************************************************
//https://github.com/fusic/maintenance/blob/3.0/src/Middleware/MaintenanceMiddleware.php#L134
//https://github.com/brussens/yii2-maintenance-mode/blob/master/src/filters/IpFilter.php#L73

// TODO : récupérer un template pour le site down ???? => https://github.com/tillkruss/framework/blob/66b75f481e6d5f5e24f320a7bba2e3104e702681/src/Illuminate/Foundation/Exceptions/views/503.blade.php

// TODO : faire une classe de Facade pour cette classe MaintenanceMode::class ?????
// TODO : renommer la classe en "Maintenance" ???? et les méthodes on/off en "up"/"down" ????
final class MaintenanceMode
{
    /** @var Filesystem */
    private $filesystem;
    /** @var MaintenanceConfig */
    private $config;
    /** @var string */
    private $path;

    public function __construct(Filesystem $filesystem, MaintenanceConfig $config)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;

        // json filepath used to store the maintenance details.
        $this->path = $config->getStoragePath();
    }

    /**
     * Turn maintenance mode "on".
     */
    public function on(): void
    {
        if ($this->isOff()) {
            // only update details if the maintenance mode is "off" to prevent update of the "time" value.
            $details = [
                'time'        => Carbon::now()->getTimestamp(),
                'message'     => $this->config->getMessage(),
                'retry_after' => $this->config->getRetryAfter(),
                'allowed_ip'  => $this->config->getAllowedIp(),
            ];

            $json = json_encode($details, JSON_PRETTY_PRINT);

            try {
                $this->filesystem->replace($this->path, $json);
            } catch (FilesystemException $e) {
                // TODO : créeret utiliser  une classe MaintenanceException
                throw new ApplicationException(
                    sprintf('Maintenance mode could not be enabled because "%s" could not be created.',  $this->path),
                    $e->getCode(),
                    $e
                );
            }
        }
    }

    /**
     * Turn maintenance mode "off".
     */
    public function off(): void
    {
        if ($this->isOn()) {
            // convert exception from filesystem to application exception.
            try {
                $this->filesystem->unlink($this->path);
            } catch (FilesystemException $e) {
                // TODO : créer et utiliser une classe MaintenanceException
                throw new ApplicationException(
                    sprintf('Maintenance mode could not be disabled because "%s" could not be removed.',  $this->path),
                    $e->getCode(),
                    $e
                );
            }
        }
    }

    /**
     * If maintenance mode is on, get the information provided when it was activated.
     * If the maintenance mode is not enabled, the returned value is an empty array.
     *
     * @return array
     */
    public function getDetails(): array
    {
        $details = [];

        if ($this->isOn()) {
            $json = $this->filesystem->read($this->path);
            $details = json_decode($json, true);
        }

        return $details;
    }

    /**
     * Check if the site is up (when maintenance mode is off).
     *
     * @return bool
     */
    public function isOff(): bool
    {
        return $this->isOn() === false;
    }

    /**
     * Check if the site is down (when maintenance mode is on).
     *
     * @return bool
     */
    public function isOn(): bool
    {
        return $this->filesystem->exists($this->path);
    }
}
