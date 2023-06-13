<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Tests\Support\Models\EntityModel;

use function PHPStan\Testing\assertType;

$class = (static fn (): string => mt_rand(0, 10) > 5 ? stdClass::class : 'Foo')();

assertType('null', model('foo'));
assertType('stdClass', model(stdClass::class));
assertType('Closure', model(Closure::class));
assertType('Tests\Support\Models\EntityModel', model(EntityModel::class));
assertType('Tests\Support\Models\EntityModel', model('EntityModel'));
assertType('null', model('App'));
assertType('object|null', model($class));
