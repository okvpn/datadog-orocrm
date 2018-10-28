<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Logging;

use Okvpn\Bundle\DatadogBundle\Logging\Watcher\ContextWatcherInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenSerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Put additional variables: current jobs, current message into datadog logger context.
 */
class OroContextWatcher implements ContextWatcherInterface
{
    /**
     * @var ContextWatcherInterface
     */
    private $wrapper;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var object
     */
    private $consumerState;

    /**
     * @var TokenSerializerInterface
     */
    private $tokenSerializer;

    /**
     * @param ContextWatcherInterface $wrapper
     */
    public function __construct(ContextWatcherInterface $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage = null)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param object|null $consumerState
     */
    public function setConsumerState(/*ConsumerState*/ $consumerState = null)
    {
        $this->consumerState = $consumerState;
    }

    /**
     * @param TokenSerializerInterface $tokenSerializer
     */
    public function setTokenSerializer(TokenSerializerInterface $tokenSerializer = null)
    {
        $this->tokenSerializer = $tokenSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function watch(): array
    {
        $context = $this->wrapper->watch();

        if (null !== $this->consumerState) {
            if ($message = $this->consumerState->getMessage()) {
                $context['consumer_message'] = $message;
            }
            if ($job = $this->consumerState->getJob()) {
                if (false === $job->isRoot()) {
                    $context['root_job'] = $job->getRootJob();
                }
                $context['job'] = $job;
            }
        }

        if (null !== $this->tokenStorage && null !== $this->tokenSerializer) {
            if ($token = $this->tokenStorage->getToken()) {
                $context['token'] = $this->tokenSerializer->serialize($token);
            }
        }

        return $context;
    }
}
