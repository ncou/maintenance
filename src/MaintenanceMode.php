<?php

declare(strict_types=1);

namespace Chiron\Maintenance;

use Chiron\Bootload\BootloaderInterface;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
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

use Chiron\Maintenance\Config\MaintenanceConfig;
use Chiron\Filesystem\Filesystem;

// TODO : récupérer un template pour le site down ???? => https://github.com/tillkruss/framework/blob/66b75f481e6d5f5e24f320a7bba2e3104e702681/src/Illuminate/Foundation/Exceptions/views/503.blade.php

// TODO : renommer la classe en "Maintenance" ???? et les méthodes on/off en "up"/"down" ????
final class MaintenanceMode
{
    /** @var Filesystem */
    private $filesystem;
    /** @var MaintenanceConfig */
    private $config;
    /** @var string */
    private $path;

    // TODO : passer en paramétre un filesystem pour gérer la partie lecture/ecriture/effacement du fichier.
    // TODO : passer en paramétre un MaintenanceConfig::class qui permettra d'initialiser les propriétés par défault pour : message/retryInseconds/allowedIps[]
    public function __construct(Filesystem $filesystem, MaintenanceConfig $config)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;

        // TODO : définir ce chemin directement dans le fichier de config ???? (on ajouterai une balise "path => string" + une balise "details => [array]" avec le détail du retry/message/allowed) ????
        $this->path = directory('@runtime/framework/down.json');
    }

    /**
     * Turn maintenance mode on.
     *
     * @param string $message
     * @param array  $allowedIpAddresses
     * @param int    $secondsToRetry
     * @return bool
     */
    //// TODO : vérifier que la valeur retry est un int supérieur à 0 ou une string avec une regex pour valider le format de la date !!!! => utiliser la rexex suivante : https://github.com/sabre-io/http/blob/master/lib/functions.php#L34      +   formatter la date si si c'est un objet DateTime::class pour avoir la date formatée correctement.
    //public function on(string $message = '', array $allowedIpAddresses = [], ?int $secondsToRetry = null)
    public function on()
    {
/*
        if ($retryAfter instanceof DateTimeInterface) {
            // TODO : à partir de la version 7.1.5 de PHP on peut utiliser la constante : \DateTime::RFC7231   qui est compliant avec la norme HTTP !!!!!!!!!!!
            //$retryAfter = $retryAfter->format('D, d M Y H:i:s \G\M\T'); //$retryAfter->format(\DateTime::RFC2822);  //'D, d M Y H:i:s e' // j'ai aussi vu un formatage en RFC1123 => gmdate(DATE_RFC1123, ...
            $retryAfter = $retryAfter->format(DATE_RFC7231);
        }
*/

        $message = $this->config->getMessage();
        $allowedIpAddresses = $this->config->getAllowed();
        $secondsToRetry = $this->config->getRetry(); // TODO : renommer la variable en "retryAfter"

        $data = $this->getDownPayload($message, $allowedIpAddresses, $secondsToRetry);

        // TODO : lever une ApplicationException ???? Plutot que d'utiliser une classe Exception qui est trop générique :( + faire un sprintf pour le message !!!!
        //if (file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT)) === false) {
        if ($this->filesystem->replace($this->path, json_encode($data, JSON_PRETTY_PRINT)) === false) {
            throw new \Exception(
                "Attention: the maintenance mode could not be enabled because {$this->path} could not be created."
            );
        }
    }

    /**
     * Turn maintenance mode off.
     *
     * @return bool
     */
    public function off()
    {
        // TODO : lever une ApplicationException ???? Plutot que d'utiliser une classe Exception qui est trop générique :( + faire un sprintf pour le message !!!!
        if (file_exists($this->path)) {
            if (! unlink($this->path)) {
                throw new \Exception(
                    "Attention: the maintenance mode could not be disabled because {$this->path} could not be removed."
                );
            };
        }
    }

    /**
     * Check if the site is down (when maintenance mode is on).
     *
     * @return bool
     */
    public function isDown(): bool
    {
        return file_exists($this->path);
    }

    /**
     * If maintenance mode is on, get the information provided when it was activated.
     *
     * @return DownPayload
     */
    // TODO : attention gérer le cas ou on essaye d'appeller cette fonction et que l'application n'est pas en maintenance, le fichier n'existant pas on va retourner de la merde lors du file_get_content !!!!
    // TODO : renommer cette méthode en getDetails() ????
    public function getDownInformation(): array
    {
        $content = file_get_contents($this->path);

        return json_decode($content, true);
    }

    /**
     * Get the payload to be placed in the "down" file.
     *
     * @param string $message
     * @param array  $allowedIpAddresses
     * @param int    $secondsToRetry
     * @return DownPayload
     */
    /*
    protected function getDownPayload($message = '', $allowedIpAddresses = [], $secondsToRetry = null)
    {
        return new DownPayload([
            'time'    => Carbon::now()->getTimestamp(),
            'message' => $message,
            'retry'   => $secondsToRetry,
            'allowed' => $allowedIpAddresses,
        ]);
    }*/


    /**
     * Get the payload to be placed in the "down" file.
     *
     * @param string $message
     * @param array  $allowedIpAddresses
     * @param int    $secondsToRetry
     *
     * @return array
     */
    // TODO : fonction à renommer en "prepareDetails()"
    private function getDownPayload(string $message = '', array $allowedIpAddresses = [], int $secondsToRetry = null): array
    {
        // TODO : il faudra surement passer en second paramétre le timezone qu'on doit récupérer dans les settings !!!!
        $now = new \DateTime('now');

        return [
            'time'    => $now->getTimestamp(), //Carbon::now()->getTimestamp(),
            'message' => $message,
            'retry'   => $secondsToRetry,
            'allowed' => $allowedIpAddresses,
        ];
    }
}
