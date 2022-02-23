<?php

class Database
{
    public $development = [...];
    public $test        = [...];
    public $production  = [...];

    public function __construct()
    {
        $this->defaultGroup = ENVIRONMENT;
    }
}
