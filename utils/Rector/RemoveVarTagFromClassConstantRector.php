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
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveVarTagFromClassConstantRector extends AbstractRector
{
    private PhpDocInfoFactory $phpDocInfoFactory;
    private DocBlockUpdater $docBlockUpdater;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater   = $docBlockUpdater;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove @var tag from class constant', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    class Foo
                    {
                    	/** @var string */
                    	const X = 'test';
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    class Foo
                    {
                    	const X = 'test';
                    }
                    CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo      = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $varTagValueNode instanceof VarTagValueNode) {
            return null;
        }

        $phpDocInfo->removeByType(VarTagValueNode::class);

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

        return $node;
    }
}
