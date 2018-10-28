<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class DatadogControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testLogException()
    {
        $request = ['message' => 'Test exception'];
        $this->client->request('POST', $this->getUrl('oro_api_datadog_test'), $request);

        $this->getJsonResponseContent($this->client->getResponse(), 200);
        $event = $this->getDatadogClient()->getLastEvent()[1];
        self::assertNotEmpty($event);
        $code = self::assertArtifact($event);

        return $code;
    }

    /**
     * @depends testLogException
     *
     * @param string $code
     */
    public function testArtifactController(string $code)
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->request('GET', $this->getUrl('okvpn_datadog_artifact', ['code' => $code]));
        $response = $this->client->getResponse();
        self::assertResponseStatusCodeEquals($response, 200);

        $artifact = $response->getContent();
        self::assertContains('userId=1', $artifact);
    }

    /**
     * @depends testArtifactController
     */
    public function testDedupLoggerApi()
    {
        $this->client->request('GET', $this->getUrl('oro_api_datadog_deduplication'));
        $response = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(1, $response);
        self::assertContains('Test exception', $response[0]);

        $this->client->request('POST', $this->getUrl('oro_api_datadog_clear'));
        $response = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(1, $response);

        $this->client->request('GET', $this->getUrl('oro_api_datadog_deduplication'));
        $response = self::getJsonResponseContent($this->client->getResponse(), 200);
        self::assertCount(0, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->getDatadogClient()->clear();
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        self::getClientInstance()->getContainer()->get('okvpn_datadog.logger')->clearDeduplicationStore();
    }

    /**
     * @return object|\Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Client\DebugDatadogClient
     */
    private function getDatadogClient()
    {
        return $this->getContainer()->get('okvpn_datadog.client_test_decorator');
    }

    protected static function assertArtifact(string $message): string
    {
        self::assertTrue((bool) preg_match('#/datadog/artifact/(\w{40})#', $message, $matches));
        self::assertArrayHasKey(1, $matches);
        return $matches[1];
    }
}
