<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @NamePrefix("oro_api_")
 */
class DatadogController extends FOSRestController
{
    /**
     * Throw test exception to test datadog send logs. <br>
     * Example usage: Send simple exception
     *
     * <pre>
     * {
     *   "message": "Exception message",
     *   "exception": "\\RuntimeException"
     * }</pre>
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Rest\Post("/logs/test", requirements={"name"="[\w-]+"})
     * @ApiDoc(
     *      description="Throw exception to test datadog send logs",
     *      resource=true
     * )
     *
     * @AclAncestor("okvpn_datadog_api")
     */
    public function testAction(Request $request)
    {
        $success = false;
        $logger = $this->container->get('okvpn_datadog.logger');
        if ($message = $request->get('message')) {
            $exception = $request->get('exception', '\RuntimeException');
            $exception = new $exception($message);
            $logger->error($message, ['exception' => $exception, 'tags' => ['http']]);
            $success = true;
        }

        return new JsonResponse([
            'success' => $success
        ]);
    }

    /**
     * Clear deduplication DatadogLogger
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Post("/logs/clear")
     * @ApiDoc(
     *      description="Clear deduplication DatadogLogger",
     *      resource=true
     * )
     *
     * @AclAncestor("okvpn_datadog_api")
     */
    public function clearAction()
    {
        $logger = $this->container->get('okvpn_datadog.logger');
        $logs = $logger->clearDeduplicationStore();
        return new JsonResponse($logs);
    }

    /**
     * Show deduplication logs
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Get("/logs/deduplication")
     * @ApiDoc(
     *      description="Show deduplication logs",
     *      resource=true
     * )
     *
     * @AclAncestor("okvpn_datadog_api")
     */
    public function deduplicationAction()
    {
        $logger = $this->container->get('okvpn_datadog.logger');
        $logs = $logger->deduplicationLogs();
        return new JsonResponse($logs);
    }
}
