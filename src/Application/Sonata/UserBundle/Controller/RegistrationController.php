<?php
/**
 * Created by PhpStorm.
 * User: luisbg
 * Date: 7/12/15
 * Time: 12:07
 * Description: Modificador del controlador de Registro de FOS UserBundle para registrar en parse.com
 */

namespace Application\Sonata\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;



/**
 * Class RegistrationController
 *
 * This class is inspired from the FOS RegistrationController
 *
 * @package Sonata\UserBundle\Controller
 *
 * @author Luis Brosa <luisbg@gmail.com>
 */

class RegistrationController extends BaseController
{
    /**
     * @return mixed
     */
    public function registerAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user instanceof UserInterface && 'POST' === $this->container->get('request')->getMethod()) {
            $this->container->get('session')->getFlashBag()->set('sonata_user_error', 'sonata_user_already_authenticated');
            $url = $this->container->get('router')->generate('sonata_user_profile_show');

            return new RedirectResponse($url);
        }
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();
            $authUser = false;
            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';
            } else {
                $authUser = true;
                $route = $this->container->get('session')->get('sonata_basket_delivery_redirect', 'sonata_user_profile_show');
                $this->container->get('session')->remove('sonata_basket_delivery_redirect');
            }




            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('session')->get('sonata_user_redirect_url');

            if (null === $url || "" === $url) {
                $url = $this->container->get('router')->generate($route);
            }

            $response = new RedirectResponse($url);

            if ($authUser) {
                $this->authenticateUser($user, $response);
            }

            return $response;
        }

        $this->container->get('session')->set('sonata_user_redirect_url', $this->container->get('request')->headers->get('referer'));

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
        ));

    }

    public function checkEmailAction()
    {
        $email = $this->container->get('session')->get('fos_user_send_confirmation_email/email');
        $this->container->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);
        d($user);
        d($email);
        exit();

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:checkEmail.html.'.$this->getEngine(), array(
            'user' => $user,
        ));
    }


}