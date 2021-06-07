<?php

declare(strict_types=1);

namespace Utils\PHPStan;

use CodeIgniter\Exceptions\FrameworkException;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

final class CheckFrameworkExceptionInstantiationViaNamedConstructorRule implements Rule
{
    /**
     * @var string
     */
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
