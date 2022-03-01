<?php

class Autoload extends AutoloadConfig
{
    public $psr4 = [
        APP_NAMESPACE => APPPATH, // For custom namespace
        'Config'      => APPPATH . 'Config',
        'Acme'        => ROOTPATH . 'acme',
    ];
    
    // ...
}
