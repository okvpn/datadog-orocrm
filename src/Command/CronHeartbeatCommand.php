<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Command;

use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\CronBundle\Command\SynchronousCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cron command that used to raise alarm when cron is not enabled.
 */
class CronHeartbeatCommand extends ContainerAwareCommand implements
    CronCommandInterface,
    SynchronousCommandInterface
{
    const COMMAND_NAME = 'oro:cron:datadog:heartbeat_check_cron';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/1 * * * *';
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->getContainer()->getParameter('okvpn_datadog.profiling');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statsd = $this->getContainer()->get('okvpn_datadog.client');
        $statsd->serviceCheck('service:cron', DogStatsInterface::STATUS_OK);
    }
}
