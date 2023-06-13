<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Config\App;
use Tests\Support\Config\Registrar;

use function PHPStan\Testing\assertType;

$class = (static fn (): string => mt_rand(0, 10) > 5 ? stdClass::class : 'Foo')();

assertType('null', config('bar'));
assertType('null', config('Foo\Bar'));
assertType('Config\App', config('App'));
assertType('Config\App', config(App::class));
assertType('Tests\Support\Config\Registrar', config('Registrar'));
assertType('Tests\Support\Config\Registrar', config(Registrar::class));
assertType('object|null', config($class));
