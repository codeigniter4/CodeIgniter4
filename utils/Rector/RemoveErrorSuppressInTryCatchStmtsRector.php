<?php

declare(strict_types=1);

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
		return new RuleDefinition('Remove error suppress @', [
			new CodeSample(
				<<<'CODE_SAMPLE'
try {
	@rmdir($dirname);
} catch (Exception $e) {}
CODE_SAMPLE
,
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
		// not in try catch
		$tryCatch = $this->betterNodeFinder->findParentType($node, TryCatch::class);
		if (! $tryCatch instanceof TryCatch)
		{
			return null;
		}

		// not in stmts, means it in catch or finally
		$inStmts = (bool) $this->betterNodeFinder->findFirst((array) $tryCatch->stmts, function (Node $n) use ($node) : bool {
			return $n === $node;
		});

		if (! $inStmts)
		{
			return null;
		}

		// in try { ... } stmts
		return $node->expr;
	}
}
