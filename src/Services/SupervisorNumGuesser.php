<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Services;

/**
 * Guesses a serial number of running consumer with using supervisor subprocess environment.
 */
class SupervisorNumGuesser implements ConsumerNumGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function processNum(): ?int
    {
        // See https://github.com/Supervisor/supervisor/blob/master/docs/subprocess.rst#subprocess-environment
        // Guesses a serial number using process name convention, like this `process_name=%(program_name)s_%(process_num)02d`
        if (!$processName = getenv('SUPERVISOR_PROCESS_NAME')) {
            return null;
        }

        if (preg_match('/(\d+)$/', $processName, $matches)) {
            if (isset($matches[1]) && is_numeric($matches[1])) {
                return (int) $matches[1];
            }
        }

        if (preg_match('/(\d+)/', $processName, $matches)) {
            if (isset($matches[1]) && is_numeric($matches[1])) {
                return (int) $matches[1];
            }
        }

        return null;
    }
}
