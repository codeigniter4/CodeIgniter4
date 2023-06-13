<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Utils\PHPStan\Type;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class FactoriesFunctionDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    private ReflectionProvider $reflectionProvider;

    /**
     * @phpstan-var array<'config'|'model', list<string>>
     */
    private array $namespaceMap = [
        'config' => ['Config\\'],
        'model'  => ['App\\Models\\'], // cannot use APP_NAMESPACE here
    ];

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;

        if (isset($_SERVER['CI_ENVIRONMENT']) && $_SERVER['CI_ENVIRONMENT'] === 'testing') {
            $this->namespaceMap['config'][] = 'Tests\\Support\\Config\\';
            $this->namespaceMap['model'][]  = 'Tests\\Support\\Models\\';
        }
    }

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return in_array($functionReflection->getName(), ['config', 'model'], true);
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
    {
        $args = $functionCall->getArgs();

        if ($args === []) {
            return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }

        $nameType = $scope->getType($args[0]->value);
        $nullType = new NullType();

        if (! $nameType->isString()->yes()) {
            return $nullType;
        }

        $constantStrings = $nameType->getConstantStrings();

        if ($constantStrings === []) {
            return $nullType;
        }

        foreach ($this->getPossibleClasses($functionReflection, $constantStrings[0]) as $possibleClass) {
            if ($this->reflectionProvider->hasClass($possibleClass)) {
                return new ObjectType($possibleClass);
            }
        }

        $isClassStringType = $nameType->isClassStringType();

        if ($isClassStringType->no()) {
            return $nullType;
        }

        if ($isClassStringType->maybe()) {
            return TypeCombinator::union(new ObjectWithoutClassType(), $nullType);
        }

        return $nameType->getClassStringObjectType();
    }

    /**
     * @return array<int, string>
     */
    private function getPossibleClasses(FunctionReflection $functionReflection, ConstantStringType $constantString): array
    {
        return array_map(
            static fn (string $namespace): string => $namespace . $constantString->getValue(),
            $this->namespaceMap[$functionReflection->getName()]
        );
    }
}
