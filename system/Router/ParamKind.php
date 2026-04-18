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

namespace CodeIgniter\Router;

/**
 * Classifies how a reflected callable parameter consumes URI segments
 * during auto-routing and dispatch.
 */
enum ParamKind
{
    case FormRequest;
    case Variadic;
    case Scalar;
}
