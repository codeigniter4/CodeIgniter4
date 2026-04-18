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

use CodeIgniter\HTTP\FormRequest;
use ReflectionParameter;

/**
 * Single source of truth for how the auto-router and the dispatcher
 * interpret a reflected callable parameter with respect to URI segment
 * consumption. Keeps AutoRouterImproved::checkParameters() and
 * CodeIgniter::resolveCallableParams() aligned on "FormRequest",
 * "variadic", and "scalar URI consumer".
 */
final class CallableParamClassifier
{
    /**
     * Returns the param kind and, when the kind is FormRequest, the resolved
     * FormRequest class name so the caller does not need to re-inspect the
     * parameter's type to inject it.
     *
     * @return array{ParamKind, class-string<FormRequest>|null}
     */
    public static function classify(ReflectionParameter $param): array
    {
        $formRequestClass = FormRequest::getFormRequestClass($param);

        if ($formRequestClass !== null) {
            return [ParamKind::FormRequest, $formRequestClass];
        }

        if ($param->isVariadic()) {
            return [ParamKind::Variadic, null];
        }

        return [ParamKind::Scalar, null];
    }
}
