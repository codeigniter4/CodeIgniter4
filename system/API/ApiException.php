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

namespace CodeIgniter\API;

use CodeIgniter\Exceptions\FrameworkException;

/**
 * Custom exception for API-related errors.
 */
final class ApiException extends FrameworkException
{
    /**
     * Thrown when the fields requested in a URL are not valid.
     */
    public static function forInvalidFields(string $field): self
    {
        return new self(lang('Api.invalidFields', [$field]));
    }

    /**
     * Thrown when the includes requested in a URL are not valid.
     */
    public static function forInvalidIncludes(string $include): self
    {
        return new self(lang('Api.invalidIncludes', [$include]));
    }

    /**
     * Thrown when an include is requested, but the method to handle it
     * does not exist on the model.
     */
    public static function forMissingInclude(string $include): self
    {
        return new self(lang('Api.missingInclude', [$include]));
    }

    /**
     * Thrown when a transformer class cannot be found.
     */
    public static function forTransformerNotFound(string $transformerClass): self
    {
        return new self(lang('Api.transformerNotFound', [$transformerClass]));
    }

    /**
     * Thrown when a transformer class does not implement TransformerInterface.
     */
    public static function forInvalidTransformer(string $transformerClass): self
    {
        return new self(lang('Api.invalidTransformer', [$transformerClass]));
    }
}
