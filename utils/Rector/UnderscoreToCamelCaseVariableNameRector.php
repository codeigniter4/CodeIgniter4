<?php

declare(strict_types=1);

namespace Utils\Rector;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Strings\StringFormatConverter;
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

    /**
     * @var ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;

    /**
     * @var StringFormatConverter
     */
    private $stringFormatConverter;

    public function __construct(
        ReservedKeywordAnalyzer $reservedKeywordAnalyzer,
        StringFormatConverter $stringFormatConverter
    ) {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->stringFormatConverter   = $stringFormatConverter;
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

        if (! Strings::contains($nodeName, '_')) {
            return null;
        }

        if ($this->reservedKeywordAnalyzer->isNativeVariable($nodeName)) {
            return null;
        }

        if ($nodeName[0] === '_') {
            return null;
        }

        $camelCaseName = $this->stringFormatConverter->underscoreAndHyphenToCamelCase($nodeName);
        if ($camelCaseName === 'this') {
            return null;
        }

        $node->name = $camelCaseName;
        $this->updateDocblock($node, $nodeName, $camelCaseName);

        return $node;
    }

    private function updateDocblock(Variable $variable, string $variableName, string $camelCaseName): void
    {
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);

        while ($parentNode) {
            /**
             * @var ClassMethod|Function_ $parentNode
             */
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);

            if ($parentNode instanceof ClassMethod || $parentNode instanceof Function_) {
                break;
            }
        }

        if ($parentNode === null) {
            return;
        }

        $docComment = $parentNode->getDocComment();
        if ($docComment === null) {
            return;
        }

        $docCommentText = $docComment->getText();
        if ($docCommentText === null) {
            return;
        }

        if (! Strings::match($docCommentText, sprintf(self::PARAM_NAME_REGEX, $variableName))) {
            return;
        }

        $phpDocInfo         = $this->phpDocInfoFactory->createFromNodeOrEmpty($parentNode);
        $paramTagValueNodes = $phpDocInfo->getParamTagValueNodes();

        foreach ($paramTagValueNodes as $paramTagValueNode) {
            if ($paramTagValueNode->parameterName === '$' . $variableName) {
                $paramTagValueNode->parameterName = '$' . $camelCaseName;
                break;
            }
        }

        $parentNode->setDocComment(new Doc($phpDocInfo->getPhpDocNode()->__toString()));
    }
}
