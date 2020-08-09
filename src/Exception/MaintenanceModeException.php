<?php

declare(strict_types=1);

namespace Chiron\Maintenance\Exception;

use Chiron\Http\Exception\Server\ServiceUnavailableHttpException;
use Carbon\Carbon;
use Throwable;

class MaintenanceModeException extends ServiceUnavailableHttpException
{
    /**
     * When the application was put in maintenance mode.
     *
     * @var \Carbon\Carbon
     */
    protected $wentDownAt;

    /**
     * The number of seconds to wait before retrying.
     *
     * @var null|int
     */
    protected $retryAfter;

    /**
     * When the application should next be available.
     *
     * @var null|\Carbon\Carbon
     */
    protected $willBeAvailableAt;

    /**
     * Create a new MaintenanceModeException instance.
     *
     * @param string    $message
     * @param int       $time
     * @param null|int  $retryAfter
     */
    public function __construct(
        string $message,
        int $time,
        ?int $retryAfter = null
    ) {
        parent::__construct($retryAfter, $message);

        $this->wentDownAt = Carbon::createFromTimestamp($time);

        if ($retryAfter) {
            $this->retryAfter = $retryAfter;
            $this->willBeAvailableAt = Carbon::createFromTimestamp($time)->addSeconds($this->retryAfter);
        }
    }

    /**
     * Get time when the application went down.
     *
     * @return \Carbon\Carbon
     */
    public function getWentDownAt(): Carbon
    {
        return $this->wentDownAt;
    }

    /**
     * Get retry after down.
     *
     * @return null|int
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * Get the time when the application is available.
     *
     * @return null|\Carbon\Carbon
     */
    public function getWillBeAvailableAt(): ?Carbon
    {
        return $this->willBeAvailableAt;
    }
}
