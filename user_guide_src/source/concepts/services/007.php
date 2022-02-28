<?php

class Services extends BaseService
{
    public static function routes()
    {
        return new \App\Router\MyRouter();
    }
}
