<?php

declare(strict_types=1);

namespace Utils\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Pass strict to function parameter on specific position argument when no value provided
 */
final class PassStrictParameterToFunctionParameterRector extends AbstractRector
{
    private const FUNCTION_WITH_ARG_POSITION = [
        // position start from 0
        'array_search'  => 2,
        'base64_decode' => 1,
        'in_array'      => 2,
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Pass strict to function parameter on specific position argument when no value provided', [
            new CodeSample('array_search($value, $array);', 'array_search($value, $array, true);'),
            new CodeSample('base64_decode($string);', 'base64_decode($string, true);'),
            new CodeSample("in_array('a', \$array);", "in_array('a', \$array, true);"),
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
        if (! method_exists($name, 'toString')) {
            return null;
        }

        $functions           = array_keys(self::FUNCTION_WITH_ARG_POSITION);
        $currentFunctionName = $name->toString();

        if (! in_array($currentFunctionName, $functions, true)) {
            return null;
        }

        $position = self::FUNCTION_WITH_ARG_POSITION[$currentFunctionName];

        if (isset($node->args[$position])) {
            return null;
        }

        $name                  = new Name('true');
        $node->args[$position] = new Arg(new ConstFetch($name));

        return $node;
    }
}
