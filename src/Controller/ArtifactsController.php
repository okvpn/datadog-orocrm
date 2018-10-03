<?php

declare(strict_types=1);

namespace Okvpn\Bridge\OroDatadogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * Download artifact via http. See Okvpn\Bridge\OroDatadogBundle\Services\ArtifactsStorageUrlDecorator
 */
class ArtifactsController extends Controller
{
    /**
     * @Route(
     *     "/artifact/{code}",
     *     name="okvpn_datadog_artifact",
     *     requirements={"code"="[a-f0-9]{40}"}
     * )
     * @AclAncestor("okvpn_datadog_api")
     * @param string $code
     * @return Response
     */
    public function artifactsAction($code)
    {
        $artifacts = $this->container->get('okvpn_datadog.logger.artifact_storage');
        if ($logs = $artifacts->getContent($code)) {
            return new Response($logs, 200, ['Content-Type' => 'text/x-log']);
        }

        return new Response('', 404, ['Content-Type' => 'text/x-log']);
    }
}
