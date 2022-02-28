<?php

class Format extends BaseConfig
{
    public $formatters = [
        'application/json' => \CodeIgniter\Format\JSONFormatter::class,
        'application/xml'  => \CodeIgniter\Format\XMLFormatter::class,
    ];
}
