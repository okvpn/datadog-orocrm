<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Tests\Functional\Demo;

use Oro\Component\MessageQueue\Consumption\AbstractExtension;
use Oro\Component\MessageQueue\Consumption\Context;

class StopConsumerExtension extends AbstractExtension
{
    /**
     * @param Context $context
     */
    public function onIdle(Context $context)
    {
        $context->setExecutionInterrupted(true);
        $context->setInterruptedReason('Queue is empty');
    }
}
