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

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Adapted from https://github.com/rectorphp/rector/blob/4578e6d8490c1acfbf59bb17c4537a672fa77193/rules/naming/src/Rector/Variable/UnderscoreToCamelCaseVariableNameRector.php
 * with skip _ in first character\
 */
final class UnderscoreToCamelCaseVariableNameRector extends AbstractRector
{
    /**
     * @see https://regex101.com/r/OtFn8I/1
     */
    private const PARAM_NAME_REGEX = '#(?<paramPrefix>@param\s.*\s+\$)(?<paramName>%s)#ms';

    private ReservedKeywordAnalyzer $reservedKeywordAnalyzer;

    public function __construct(
        ReservedKeywordAnalyzer $reservedKeywordAnalyzer
    ) {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change under_score names to camelCase', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run($a_b)
                        {
                            $some_value = $a_b;
                        }
                    }
                    CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run($aB)
                        {
                            $someValue = $aB;
                        }
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
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return null;
        }

        if ($this->reservedKeywordAnalyzer->isNativeVariable($nodeName)) {
            return null;
        }

        $underscorePosition = strpos($nodeName, '_');
        // underscore not found, or in the first char, skip
        if ((int) $underscorePosition === 0) {
            return null;
        }

        $replaceUnderscoreToSpace = str_replace('_', ' ', $nodeName);
        $uppercaseFirstChar       = ucwords($replaceUnderscoreToSpace);
        $camelCaseName            = lcfirst(str_replace(' ', '', $uppercaseFirstChar));

        if ($camelCaseName === 'this') {
            return null;
        }

        $node->name = $camelCaseName;
        $this->updateDocblock($node, $nodeName, $camelCaseName);

        return $node;
    }

    private function updateDocblock(Variable $variable, string $variableName, string $camelCaseName): void
    {
        $parentClassMethodOrFunction = $this->betterNodeFinder->findParentByTypes($variable, [ClassMethod::class, Function_::class]);

        if ($parentClassMethodOrFunction === null) {
            return;
        }

        $docComment = $parentClassMethodOrFunction->getDocComment();
        if ($docComment === null) {
            return;
        }

        $docCommentText = $docComment->getText();
        if ($docCommentText === null) {
            return;
        }

        if (! preg_match(sprintf(self::PARAM_NAME_REGEX, $variableName), $docCommentText)) {
            return;
        }

        $phpDocInfo         = $this->phpDocInfoFactory->createFromNodeOrEmpty($parentClassMethodOrFunction);
        $paramTagValueNodes = $phpDocInfo->getParamTagValueNodes();

        foreach ($paramTagValueNodes as $paramTagValueNode) {
            if ($paramTagValueNode->parameterName === '$' . $variableName) {
                $paramTagValueNode->parameterName = '$' . $camelCaseName;
                break;
            }
        }

        $parentClassMethodOrFunction->setDocComment(new Doc($phpDocInfo->getPhpDocNode()->__toString()));
    }
}
