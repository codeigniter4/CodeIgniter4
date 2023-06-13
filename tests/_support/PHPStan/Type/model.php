<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use function PHPStan\Testing\assertType;

$class = (static fn (): string => mt_rand(0, 10) > 5 ? stdClass::class : 'Foo')();

assertType('null', model('foo'));
assertType('stdClass', model(stdClass::class));
assertType('Closure', model(Closure::class));
assertType('null', model('App'));
assertType('object|null', model($class));

// don't use test models used in other tests, as MemoizingReflectionProvider
// messes autoload causing unknown class errors
assertType('Tests\Support\Models\BarModel', model('BarModel'));
