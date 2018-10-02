<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Services;

use Okvpn\Bundle\DatadogBundle\Logging\ArtifactsStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArtifactsStorageUrlDecorator implements ArtifactsStorageInterface
{
    private $artifactsStorage;
    private $container;

    public function __construct(ArtifactsStorageInterface $artifactsStorage, ContainerInterface $container)
    {
        $this->artifactsStorage = $artifactsStorage;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $content): string
    {
        $code = $this->artifactsStorage->save($content);
        if ($url = $this->generateFullUrl($code)) {
            return $url;
        }

        return $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(string $code): ?string
    {
        return $this->artifactsStorage->getContent($code);
    }

    private function generateFullUrl(string $code): ?string
    {
        try {
            $configManager = $this->container->get('oro_config.global');
            $uri = $this->container->get('router')->generate('okvpn_datadog_artifact', ['code' => $code]);
            return $configManager->get('oro_ui.application_url') . $uri;
        } catch (\Exception $exception) {} //Skip, for example database is not available

        return null;
    }
}
