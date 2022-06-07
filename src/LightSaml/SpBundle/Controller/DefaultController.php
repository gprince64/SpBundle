<?php

/*
 * This file is part of the LightSAML SP-Bundle package.
 *
 * (c) Milos Tomic <tmilos@lightsaml.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LightSaml\SpBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Container\ContainerInterface;

class DefaultController extends AbstractController
{
    public function metadataAction(ContainerInterface $container)
    {
        $profile = $container->get('ligthsaml.profile.metadata');
        $context = $profile->buildContext();
        $action = $profile->buildAction();

        $action->execute($context);

        return $context->getHttpResponseContext()->getResponse();
    }

    public function discoveryAction(ContainerInterface $container)
    {
        $parties = $container->get('lightsaml.container.build')->getPartyContainer()->getIdpEntityDescriptorStore()->all();

        if (1 == count($parties)) {
            return $this->redirect($this->generateUrl('lightsaml_sp.login', ['idp' => $parties[0]->getEntityID()]));
        }

        return $this->render('@LightSamlSp/discovery.html.twig', [
            'parties' => $parties,
        ]);
    }

    public function loginAction(Request $request, ContainerInterface $container)
    {
        $idpEntityId = $request->get('idp');
        if (null === $idpEntityId) {
            return $this->redirect($this->generateUrl($container->getParameter('lightsaml_sp.route.discovery')));
        }

        $profile = $container->get('ligthsaml.profile.login_factory')->get($idpEntityId);
        $context = $profile->buildContext();
        $action = $profile->buildAction();

        $action->execute($context);

        return $context->getHttpResponseContext()->getResponse();
    }

    public function sessionsAction(ContainerInterface $container)
    {
        $ssoState = $container->get('lightsaml.container.build')->getStoreContainer()->getSsoStateStore()->get();

        return $this->render('@LightSamlSp/sessions.html.twig', [
            'sessions' => $ssoState->getSsoSessions(),
        ]);
    }
}
