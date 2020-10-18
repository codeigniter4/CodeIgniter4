<?php

declare(strict_types=1);

namespace Utils\PHPStan;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

final class CheckUseStatementsAfterLicenseRule implements Rule
{
	/**
	 * @var string
	 */
	private const ERROR_MESSAGE = 'Use statement must be located after license docblock';

	public function getNodeType(): string
	{
		return Stmt::class;
	}

	/**
	 * @param Stmt $node
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$comments = $node->getAttribute('comments');
		if ($comments === [])
		{
			return [];
		}

		foreach ($comments as $comment)
		{
			if (! $comment instanceof Doc)
			{
				continue;
			}

			$text = $comment->getText();
			if (! preg_match('/\* Copyright \(c\) 2019-\d{4,} CodeIgniter Foundation\n/', $text))
			{
				continue;
			}

			$previous = $node->getAttribute('previous');
			while ($previous)
			{
				if ($previous instanceof Use_)
				{
					return [self::ERROR_MESSAGE];
				}

				$previous = $previous->getAttribute('previous');
			}
		}

		return [];
	}
}
