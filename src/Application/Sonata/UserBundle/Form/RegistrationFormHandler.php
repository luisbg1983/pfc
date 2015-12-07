<?php
/**
 * Created by PhpStorm.
 * User: luisbg
 * Date: 7/12/15
 * Time: 16:25
 * Description: Modificación del Handler para que registre el usuario en Parse.com
 */


namespace Application\Sonata\UserBundle\Form;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as HandlerBase;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;

class RegistrationFormHandler extends HandlerBase
{
    protected $request;
    protected $userManager;
    protected $form;
    protected $mailer;
    protected $tokenGenerator;

    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $app_id="1cqYcf66haVHbCdBVLfsf2ftqRDvbjSbS6FtBLSK";
        $rest_key="dXjZTafqEjH6JEko0OJhPjmQtosUfnaL8Z3tQwb5";
        $master_key="xxuLNI0bAuPYHTPu08NlU8YxdKEGFnripFIdOKH1";
        ParseClient::initialize( $app_id, $rest_key, $master_key );
        parent::__construct($form,$request,$userManager,$mailer,$tokenGenerator);
    }

    public function process($confirmation = false)
    {

        $user = $this->createUser();
        d($user);
        $this->form->setData($user);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user, $confirmation);

                return true;
            }
        }

        return false;
    }


    /**
     * @param boolean $confirmation
     */
    protected function onSuccess(UserInterface $user, $confirmation)
    {


        if ($confirmation) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->mailer->sendConfirmationEmailMessage($user);
        } else {
            $user->setEnabled(true);
        }

        $this->userManager->updateUser($user);
    }

    /**
     * @return UserInterface
     */
    protected function createUser()
    {
      //
        //PRIMERA PRUEBA DE PARSE.com

        // Signup

        //ASIGNAMOS variables del request:
        $formulario = $this->request->get('fos_user_register_form');
        $username = $formulario['username'];
        $email = $formulario['email'];
        if($formulario['plainPassword']['first'] === $formulario['plainPassword']['second'] ){
            $password = $formulario['plainPassword']['first'];
        }else{
            return false;
        }

        $user = new ParseUser();
        $user->setUsername($username);
        $user->setemail($email);
        $user->setPassword($password);
        try {
            $user->signUp();
        } catch (ParseException $ex) {
            // error in $ex->getMessage();
        }
        d($user);
        //POR AHORA DEJO TB CREANDO EL USUARIO EN LA BD LOCAL DE SYMFONY
        return $this->userManager->createUser();
    }



}