<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Config\BaseConfig;

class SimpleConfig extends BaseConfig
{
    public ?string $QZERO           = null;
    public ?string $QZEROSTR        = null;
    public ?string $QEMPTYSTR       = null;
    public bool|string|null $QFALSE = null;
    public string $first            = 'foo';
    public string $second           = 'bar';
    public ?string $FOO             = null;
    public ?string $onedeep         = null;

    /**
     * @var array<string, string|null>
     */
    public array $default = [
        'name' => null,
    ];

    /**
     * @var array<string, string|null>
     */
    public array $simple = [
        'name' => null,
    ];

    // properties for environment override testing
    public string $alpha        = 'one';
    public string $bravo        = 'two';
    public string $charlie      = 'three';
    public string $delta        = 'four';
    public string $echo         = '';
    public bool|string $foxtrot = 'false';
    public string $fruit        = 'pineapple';
    public string $dessert      = '';
    public int $golf            = 18;

    /**
     * @var array<string, bool|string>
     */
    public array $crew = [
        'captain' => 'Kirk',
        'science' => 'Spock',
        'doctor'  => 'Bones',
        'comms'   => 'Uhuru',
    ];

    public ?string $shortie       = null;
    public ?string $longie        = null;
    public ?string $onedeep_value = null;

    /**
     * @var array<string, string|null>
     */
    public array $one_deep = [
        'under_deep' => null,
    ];

    public float $float     = 12.34;
    public int $int         = 1234;
    public string $password = 'secret';
    public ?int $size       = null;
}
