<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Tests\Functional\Demo;

use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

class DatadogMessageProcessor implements MessageProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = json_decode($message->getBody(), true);

        switch ($body['flag']) {
            case 'exception':
                throw new \RuntimeException('Test handle consumer exceptions');
                break;
            case 'throwable':
                \function_do_not_exist();
                return self::ACK;
                break;
            default:
                return self::ACK;
        }
    }
}
