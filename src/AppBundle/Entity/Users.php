<?php
/**
 * Created by PhpStorm.
 * User: macbook51
 * Date: 30/01/16
 * Time: 21:16
 */

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;


class Users extends BaseUser
{

    protected $id;

    public function __construct()
    {
        parent::__construct();
    }

}