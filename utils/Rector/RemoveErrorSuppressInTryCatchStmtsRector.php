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
use PhpParser\Node\Stmt\TryCatch;
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
        return [ErrorSuppress::class];
    }

    /**
     * @param ErrorSuppress $node
     */
    public function refactor(Node $node): ?Node
    {
        $tryCatch = $this->betterNodeFinder->findParentType($node, TryCatch::class);

        // not in try catch
        if (! $tryCatch instanceof TryCatch) {
            return null;
        }

        $inStmts = (bool) $this->betterNodeFinder->findFirst((array) $tryCatch->stmts, static fn (Node $n): bool => $n === $node);

        // not in stmts, means it in catch or finally
        if (! $inStmts) {
            return null;
        }

        // in try { ... } stmts
        return $node->expr;
    }
}
