<?php
/**
 * Created by PhpStorm.
 * User: luisbg
 * Date: 7/12/15
 * Time: 12:07
 */

namespace Application\Sonata\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
//use Sonata\UserBundle\Controller\RegistrationFOSUser1Controller as BaseController;


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
        $response = parent::registerAction();
        return $response;
    }


}