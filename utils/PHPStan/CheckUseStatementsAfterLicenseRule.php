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

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

final class CheckUseStatementsAfterLicenseRule implements Rule
{
    private const ERROR_MESSAGE   = 'Use statement must be located after license docblock';
    private const COPYRIGHT_REGEX = '/\* \(c\) CodeIgniter Foundation <admin@codeigniter\.com>/m';

    public function getNodeType(): string
    {
        return Stmt::class;
    }

    /**
     * @param Stmt $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $comments = $node->getComments();

        if ($comments === []) {
            return [];
        }

        foreach ($comments as $comment) {
            if (! $comment instanceof Doc) {
                continue;
            }

            if (! preg_match(self::COPYRIGHT_REGEX, $comment->getText())) {
                continue;
            }

            $previous = $node->getAttribute('previous');

            while ($previous) {
                if ($previous instanceof Use_) {
                    return [self::ERROR_MESSAGE];
                }

                $previous = $previous->getAttribute('previous');
            }
        }

        return [];
    }
}
