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

namespace Utils\PHPStan;

use CodeIgniter\Exceptions\FrameworkException;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

final class CheckFrameworkExceptionInstantiationViaNamedConstructorRule implements Rule
{
    private const ERROR_MESSAGE = 'FrameworkException instance creation via new expression is not allowed, use its named constructor instead';

    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->class;
        if (! $class instanceof FullyQualified) {
            return [];
        }

        if (! is_a((string) $class, FrameworkException::class, true)) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }
}
