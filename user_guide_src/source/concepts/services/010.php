<?php

class Services
{
    public static function routes($getShared = false)
    {
        if (! $getShared) {
            return new \CodeIgniter\Router\RouteCollection();
        }

        return static::getSharedInstance('routes');
    }
}
