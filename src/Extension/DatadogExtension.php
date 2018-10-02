<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Extension;

use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;
use Okvpn\Bundle\DatadogBundle\Logging\ErrorBag;
use Oro\Component\MessageQueue\Client\Config;
use Oro\Component\MessageQueue\Consumption\AbstractExtension;
use Oro\Component\MessageQueue\Consumption\Context;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class DatadogExtension extends AbstractExtension
{
    protected $statsd;
    protected $errorBag;
    protected $container;
    protected $stopwatch;
    protected $pid;

    public function __construct(DogStatsInterface $statsd, ErrorBag $errorBag, ContainerInterface $container) //Inject only container into persistent service
    {
        $this->statsd = $statsd;
        $this->container = $container;
        $this->errorBag = $errorBag;
        $this->pid = getmypid();
        $this->stopwatch = new Stopwatch();
    }

    /**
     * {@inheritdoc}
     */
    public function onPreReceived(Context $context)
    {
        $this->stopwatch->start('message_received');
    }

    /**
     * {@inheritdoc}
     */
    public function onPostReceived(Context $context)
    {
        $event = $this->stopwatch->stop('message_received');
        $this->stopwatch->reset();

        switch ($context->getStatus()) {
            case MessageProcessorInterface::ACK:
                $tags = ['mq:ask'];
                break;
            case MessageProcessorInterface::REJECT:
                $tags = ['mq:ask'];
                break;
            case MessageProcessorInterface::REQUEUE:
                $tags = ['mq:requeue'];
                break;
            default:
                $tags = ['mq:none'];
                break;
        }

        $this->statsd->timing('mq.messages', round($event->getDuration()/1000.0, 4), $tags);
        $this->statsd->gauge('mq.mem', $event->getMemory());
        $this->statsd->set('consumers', $this->pid);

        $this->flushError();
    }

    /**
     * {@inheritdoc}
     */
    public function onIdle(Context $context)
    {
        $this->statsd->set('consumers', $this->pid);
        $this->statsd->timing('mq.messages', 0, ['mq:idle']);
    }

    /**
     * {@inheritdoc}
     */
    public function onInterrupted(Context $context)
    {
        if ($exception = $context->getException()) {
            try {
                $logger = $this->container->get('okvpn_datadog.logger');
                $processor = str_replace('.', '_', $context->getMessage()->getProperty(Config::PARAMETER_PROCESSOR_NAME, 'na'));
                $logger->error(
                    $exception->getMessage(),
                    [
                        'tags' => ['error:consumer', 'processor:' . $processor],
                        'exception' => $exception
                    ]
                );
            } catch (\Throwable $exception) {}
        }
    }

    protected function flushError()
    {
        if ($record = $this->errorBag->rootError()) {
            $logger = $this->container->get('okvpn_datadog.logger');
            try {
                $this->errorBag->flush();
                $context = $record['context'];
                $context['tags'] = ['error:consumer', 'channel:' . $record['channel']];
                $logger->warning($record['message'], $context);
            } catch (\Exception $exception) {}
        }
    }
}
