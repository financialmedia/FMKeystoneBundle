<?php

namespace FM\KeystoneBundle\Controller;

use FM\KeystoneBundle\Model\Token;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenController extends Controller
{
    public function tokensAction()
    {
        $security = $this->get('security.context');

        $token = null;

        if (!$security->getToken() || !(($user = $security->getToken()->getUser()) && $security->isGranted('ROLE_USER'))) {
            return new Response('Unauthorized', 401);
        }

        $manager = $this->getTokenManager();
        $token = $manager->createToken($user, 3600);

        if (!$token) {
            return new Response('Error obtaining token', 500);
        }

        $data = array(
            'access' => array(
                'token' => $this->getTokenData($token),
                'user' => $this->getUserData($user),
                'serviceCatalog' => $this->getServiceCatalog($token)
            )
        );

        $response = new Response();
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Vary', 'X-Auth-Token');

        return $response;
    }

    /**
     * @return FM\KeystoneBundle\Manager\TokenManager
     */
    protected function getTokenManager()
    {
        return $this->get('fm_keystone.token_manager');
    }

    protected function getTokenData(Token $token)
    {
        return array(
            'id' => $token->getId(),
            'expires' => $token->getExpiresAt()->format(\DateTime::ISO8601)
        );
    }

    protected function getServiceCatalog(Token $token)
    {
        $q = $this->get('doctrine')->getManager()->createQuery('SELECT s, e FROM FMKeystoneBundle:Service s JOIN s.endpoints e');
        $q->useResultCache(true, null, 3600);
        $services = $q->getResult();

        $catalog = array();
        foreach ($services as $service) {
            $endpoints = array();
            foreach ($service->getEndpoints() as $endpoint) {
                $endpoints[] = array(
                    'adminUrl' => $endpoint->getAdminUrl(),
                    'publicUrl' => $endpoint->getPublicUrl()
                );
            }

            $catalog[] = array(
                'name' => $service->getName(),
                'type' => $service->getType(),
                'endpoints' => $endpoints,
            );
        }

        return $catalog;
    }

    protected function getUserData(UserInterface $user)
    {
        return array(
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'name' => (string) $user,
            'roles' => array()
        );
    }

    protected function getContainer()
    {
        return $this->container;
    }
}
