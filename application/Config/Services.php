<?php namespace Config;

use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Config\BaseConfig;

require_once BASEPATH.'Config/Services.php';

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends CoreServices
{

//    public static function example($getShared = true)
//    {
//        if ($getShared)
//        {
//            return self::getSharedInstance('example');
//        }
//
//        return new \CodeIgniter\Example();
//    }

    public static function honeypot(BaseConfig $config = null, $getShared = true)
    {
        if ($getShared)
        {
            return self::getSharedInstance('honeypot', $config);
        }

        if (is_null($config)) 
        {
            $config = new \Config\Honeypot();
        }

        return new \CodeIgniter\Honeypot\Honeypot($config);
    }


}
