<?php
/**
 * Created by PhpStorm.
 * User: luisbg
 * Date: 11/12/15
 * Time: 18:12
 */

/*
 * Se Modifica el Symfony/Bundle/SecurityBundle/Resources/config/security_listener..xml
 * Este:
 *  <parameter key="security.authentication.listener.form.class">Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener</parameter>
 * Por:
 * <parameter key="security.authentication.listener.form.class">Application\Sonata\UserBundle\Security\LoginParseListener</parameter>
 *
 *
 */

namespace Application\Sonata\UserBundle\Security;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener as LoginBase;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderAdapter;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseClient;
use Symfony\Component\DependencyInjection\ContainerInterface;



Class LoginParseListener extends LoginBase {

    private $csrfTokenManager;
    private $container;
    private $eventDispatcher;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, SessionAuthenticationStrategyInterface $sessionStrategy, HttpUtils $httpUtils, $providerKey, AuthenticationSuccessHandlerInterface $successHandler, AuthenticationFailureHandlerInterface $failureHandler, array $options = array(), LoggerInterface $logger = null, EventDispatcherInterface $dispatcher = null, $csrfTokenManager = null, ContainerInterface $container)
    {
        $this->container = $container;
        $this->eventDispatcher = $dispatcher;
        $app_id = $this->container->getParameter('parse_app_id');
        $rest_key = $this->container->getParameter("parse_rest_key");
        $master_key = $this->container->getParameter("parse_master_key");
        ParseClient::initialize( $app_id , $rest_key, $master_key );
        parent::__construct($tokenStorage, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey, $successHandler,$failureHandler,$options , $logger ,$dispatcher , $csrfTokenManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {

        if (null !== $this->csrfTokenManager) {
            $csrfToken = ParameterBagUtils::getRequestParameterValue($request, $this->options['csrf_parameter']);

            if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken($this->options['csrf_token_id'], $csrfToken))) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }
        }

        if ($this->options['post_only']) {
            $username = trim(ParameterBagUtils::getParameterBagValue($request->request, $this->options['username_parameter']));
            $password = ParameterBagUtils::getParameterBagValue($request->request, $this->options['password_parameter']);
        } else {
            $username = trim(ParameterBagUtils::getRequestParameterValue($request, $this->options['username_parameter']));
            $password = ParameterBagUtils::getRequestParameterValue($request, $this->options['password_parameter']);
        }

        //Lo que Voy a hacer es Autentificar en Parse y si es OK, Se loguea tb en BBDD

        try {
            $user = ParseUser::logIn($username, $password);
            // Do stuff after successful login.
        } catch (ParseException $error) {
            // The login failed. Check error to see why.
            if($error->getCode() === 101)
            {
                //ERROR CREDENCIALES --> FORZAMOS UNA PASSWORD ERRONEA EN EL authenticate de SYMFONY para que no logue.
                return $this->authenticationManager->authenticate(new UsernamePasswordToken($username, null, $this->providerKey));
            }else{
                throw($error);
            }
        }

        $request->getSession()->set(Security::LAST_USERNAME, $username);


        return $this->authenticationManager->authenticate(new UsernamePasswordToken($username, $password, $this->providerKey));
    }

}