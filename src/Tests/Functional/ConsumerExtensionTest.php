<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Tests\Functional;

use Okvpn\Bridge\OroDatadogBundle\Tests\Functional\Demo\StopConsumerExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\ChainExtension;
use Oro\Component\MessageQueue\Consumption\Extension\LoggerExtension;
use Oro\Component\MessageQueue\Consumption\QueueConsumer;
use Psr\Log\NullLogger;

class ConsumerExtensionTest extends WebTestCase
{
    /** @var QueueConsumer */
    private $consumer;
    private $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();
        $container = self::getContainer();

        if (!$container->hasParameter('message_queue_transport') ||
            $container->getParameter('message_queue_transport') === 'null'
        ) {
            $this->markTestSkipped('The null message queue transport is not allow for tests');
        }

        $consumer = $container->get('oro_message_queue.client.queue_consumer');
        $registry = $container->get('oro_message_queue.client.meta.destination_meta_registry');
        foreach ($registry->getDestinationsMeta() as $destinationMeta) {
            $consumer->bind(
                $destinationMeta->getTransportName(),
                $container->get('oro_message_queue.client.delegate_message_processor')
            );
        }

        $extensions[] = new LoggerExtension(new NullLogger());
        $extensions[] = new StopConsumerExtension();

        $conn = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getConnection();
        $conn->executeQuery('delete from oro_message_queue');
        $conn->executeQuery('delete from oro_message_queue_job_unique');
        $conn->executeQuery('delete from oro_message_queue_job');

        $this->consumer = $consumer;
        $this->extension = new ChainExtension($extensions);
    }

    public function testProcess()
    {
        $producer = $this->getContainer()->get('oro_message_queue.client.message_producer');
        $producer->send('okvpn_datadog_test', ['flag' => '']);

        $this->consumer->consume($this->extension);
        $records = $this->getDatadogClient()->getRecords();
        self::assertNotEmpty($records);
        $args = array_column($records, 'args');
        $metrics = array_column($args, 0);

        self::assertContains('mq.messages', $metrics);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->getDatadogClient()->clear();
    }

    /**
     * @return object|\Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Client\DebugDatadogClient
     */
    private function getDatadogClient()
    {
        return $this->getContainer()->get('okvpn_datadog.client_test_decorator');
    }
}
