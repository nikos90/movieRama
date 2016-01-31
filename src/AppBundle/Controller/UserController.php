<?php
/**
 * Created by PhpStorm.
 * User: macbook51
 * Date: 30/01/16
 * Time: 21:53
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    public function loginAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render(
            'AppBundle:user:login.html.twig');
    }


}
