<?php
/**
 * Created by PhpStorm.
 * User: luisbg
 * Date: 7/12/15
 * Time: 11:14
 */

namespace Application\Sonata\UserBundle\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine; // for Symfony 2.1.0+

class RegisterSub implements EventSubscriberInterface{


    private $router;

    /**
     * Constructor
     *
     * @param router
     */

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        //return the subscribed events, their methods and priorities
        return array(

        );

    }

    public function onRegisterDone(FilterUserResponseEvent $event)
    {
        //$event->setResponse(new RedirectResponse($url));
    }





}