<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Utils\PhpCsFixer\Fixer\Comment;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

/**
 * @internal
 */
final class SpaceAfterCommentStartFixer extends AbstractFixer
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'CodeIgniter4/' . parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should be a single whitespace after the comment start',
            [new CodeSample("<?php\n    //this is a comment\n")]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_COMMENT);
    }

    /**
     * {@inheritDoc}
     *
     * Must run after NoEmptyCommentFixer
     */
    public function getPriority(): int
    {
        return 3;
    }

    /**
     * {@inheritDoc}
     */
    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = $tokens->count(); $index < $count; $index++) {
            /** @var Token $token */
            $token = $tokens[$index];

            if (! $token->isGivenKind(T_COMMENT)) {
                continue;
            }

            $comment = $token->getContent();

            if (substr($comment, 0, 2) !== '//') {
                continue;
            }

            if (Preg::match('/^\/\/(?!\s+)(.+)/', $comment, $matches) !== 1) {
                continue;
            }

            if (Preg::match('/\-+/', $matches[1]) === 1 || Preg::match('/\=+/', $matches[1]) === 1) {
                continue;
            }

            $tokens[$index] = new Token([T_COMMENT, '// ' . $matches[1]]);
        }
    }
}
