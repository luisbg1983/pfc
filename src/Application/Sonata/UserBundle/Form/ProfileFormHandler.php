<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\UserBundle\Form;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;
use Symfony\Component\HttpFoundation\Session\Session;
use FOS\UserBundle\Form\Handler\ProfileFormHandler as HandlerBase;

class ProfileFormHandler extends HandlerBase
{
    protected $request;
    protected $userManager;
    protected $form;
    protected $session;
    protected $container;

    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager, Session $session, ContainerInterface $container)
    {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->session = $session;
        $this->container = $container;
        $app_id = $this->container->getParameter('parse_app_id');
        $rest_key = $this->container->getParameter("parse_rest_key");
        $master_key = $this->container->getParameter("parse_master_key");
        ParseClient::initialize( $app_id , $rest_key, $master_key );

    }

    public function process(UserInterface $user)
    {
        //$this->form->setData($user);
        $this->UserToParseUser($user);
        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }

            // Reloads the user to reset its username. This is needed when the
            // username or password have been changed to avoid issues with the
            // security layer.
            $this->userManager->reloadUser($user);
        }

        return false;
    }

    protected function onSuccess(UserInterface $user)
    {
        $this->userManager->updateUser($user);
    }

    protected function UserToParseUser(UserInterface $user){
        $token = $this->session->get('parseUser')->getSessionToken();
        try {
            $user2 = ParseUser::become($token);
            // The current user is now set to user.
        } catch (ParseException $ex) {
            // The token could not be validated.
        }
        //AQUI ASIGNAMOS TODOS LOS VALORES PARA HACER EL UPDATE.

        d($user);
        return $user;
    }
}
