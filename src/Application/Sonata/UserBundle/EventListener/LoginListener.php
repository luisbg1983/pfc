<?php

namespace Application\Sonata\UserBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine; // for Symfony 2.1.0+
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Custom login listener.
 */
class LoginListener
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    private $securityContext;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    private $container;
    private $session;

    /**
     * Constructor
     *
     * @param SecurityContext $securityContext
     * @param Doctrine        $doctrine
     */
    public function __construct(SecurityContext $securityContext, Doctrine $doctrine,ContainerInterface $container,Session $session)
    {
        $this->container = $container;
        $this->session = $session;
        $app_id = $this->container->getParameter('parse_app_id');
        $rest_key = $this->container->getParameter("parse_rest_key");
        $master_key = $this->container->getParameter("parse_master_key");
        ParseClient::initialize( $app_id , $rest_key, $master_key );
        $this->securityContext = $securityContext;
        $this->em              = $doctrine->getEntityManager();

    }

    /**
     * Do the magic.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // Configuramos una Variable de Session para el Usuario Actual de Parse
            $this->session->set('parseUser', ParseUser::getCurrentUser());
        }

        if ($this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // user has logged in using remember_me cookie
        }

        // do some other magic here
        $user = $event->getAuthenticationToken()->getUser();

        // ...
    }


}