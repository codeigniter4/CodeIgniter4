<?php

declare(strict_types=1);

namespace Utils\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * Pass true to 3rd parameter of in_array when no value provided
 */
final class PassTrueToThirdParameterInArrayRector extends AbstractRector
{
	public function getDefinition(): RectorDefinition
	{
		return new RectorDefinition('Pass true to 3rd parameter of in_array if no value provided', [
			new CodeSample(
				<<<'CODE_SAMPLE'
in_array('a', $array);
CODE_SAMPLE
,
				<<<'CODE_SAMPLE'
in_array('a', $array, true);
CODE_SAMPLE
			),
		]);
	}

	/**
	 * @return string[]
	 */
	public function getNodeTypes(): array
	{
		return [FuncCall::class];
	}

	/**
	 * @param FuncCall $node
	 */
	public function refactor(Node $node): ?Node
	{
		$name = $node->name;
		if (! method_exists($name, 'toString'))
		{
			return null;
		}

		if ($name->toString() !== 'in_array')
		{
			return null;
		}

		if (isset($node->args[2]))
		{
			return null;
		}

		$name          = new Name('true');
		$node->args[2] = new Arg(new ConstFetch($name));

		return $node;
	}
}
