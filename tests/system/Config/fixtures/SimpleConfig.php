<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

class SimpleConfig extends \CodeIgniter\Config\BaseConfig
{
    public $QZERO;
    public $QZEROSTR;
    public $QEMPTYSTR;
    public $QFALSE;
    public $first  = 'foo';
    public $second = 'bar';
    public $FOO;
    public $onedeep;
    public $default = [
        'name' => null,
    ];
    public $simple = [
        'name' => null,
    ];

    // properties for environment override testing
    public $alpha   = 'one';
    public $bravo   = 'two';
    public $charlie = 'three';
    public $delta   = 'four';
    public $echo    = '';
    public $foxtrot = 'false';
    public $fruit   = 'pineapple';
    public $dessert = '';
    public $golf    = 18;
    public $crew    = [
        'captain' => 'Kirk',
        'science' => 'Spock',
        'doctor'  => 'Bones',
        'comms'   => 'Uhuru',
    ];
    public $shortie;
    public $longie;
    public $onedeep_value;
    public $one_deep = [
        'under_deep' => null,
    ];
    public $float     = 12.34;
    public $int       = 1234;
    public $password  = 'secret';
    public ?int $size = null;
}
