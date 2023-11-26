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

namespace Utils\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveErrorSuppressInTryCatchStmtsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove error suppression operator `@` inside try...catch blocks', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    	try {
                    		@rmdir($dirname);
                    	} catch (Exception $e) {}
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    try {
                    	rmdir($dirname);
                    } catch (Exception $e) {}
                    CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [TryCatch::class];
    }

    /**
     * @param TryCatch $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;

        $this->traverseNodesWithCallable(
            $node->stmts,
            static function (Node $subNode) use (&$hasChanged) {
                if ($subNode instanceof Class_ || $subNode instanceof Function_) {
                    return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }

                if ($subNode instanceof ErrorSuppress) {
                    $hasChanged = true;

                    return $subNode->expr;
                }

                return null;
            }
        );

        if ($hasChanged) {
            return $node;
        }

        return null;
    }
}
