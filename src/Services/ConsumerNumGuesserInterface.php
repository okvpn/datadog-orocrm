<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Services;

/**
 * Guesses a serial number of running consumer. Guessing errors must be not critical
 *
 * This interface can be use for gauges metrics https://docs.datadoghq.com/developers/metrics/gauges/
 */
interface ConsumerNumGuesserInterface
{
    /**
     * Return integer serial number of consumer or null if not possible guess a number.
     *
     * @return int
     */
    public function processNum(): ?int;
}
