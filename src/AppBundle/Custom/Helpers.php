<?php
/**
 * Created by PhpStorm.
 * User: macbook51
 * Date: 31/01/16
 * Time: 01:56
 */

 namespace AppBundle\Custom;

 class Helpers{
     public static  function die_pre($data){
         echo '<pre>';
         print_r($data);
         echo '</pre>';
         exit;
     }
 }