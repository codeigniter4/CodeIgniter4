<?php

class App extends BaseConfig
{
    public $sessionDriver   = 'CodeIgniter\Session\Handlers\MemcachedHandler';
    public $sessionSavePath = 'localhost:11211';
}
