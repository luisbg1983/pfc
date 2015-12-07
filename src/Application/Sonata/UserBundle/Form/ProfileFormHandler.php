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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;
use FOS\UserBundle\Form\Handler\ProfileFormHandler as HandlerBase;

class ProfileFormHandler extends HandlerBase
{
    protected $request;
    protected $userManager;
    protected $form;

    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager)
    {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $app_id="1cqYcf66haVHbCdBVLfsf2ftqRDvbjSbS6FtBLSK";
        $rest_key="dXjZTafqEjH6JEko0OJhPjmQtosUfnaL8Z3tQwb5";
        $master_key="xxuLNI0bAuPYHTPu08NlU8YxdKEGFnripFIdOKH1";
        ParseClient::initialize( $app_id, $rest_key, $master_key );
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
        $currentUser = ParseUser::getCurrentUser();
        d($currentUser);
        exit();
        return $user;
    }
}
