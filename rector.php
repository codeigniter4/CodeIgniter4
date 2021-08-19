<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Utils\Rector\PassStrictParameterToFunctionParameterRector;
use Utils\Rector\RemoveErrorSuppressInTryCatchStmtsRector;
use Utils\Rector\RemoveVarTagFromClassConstantRector;
use Utils\Rector\UnderscoreToCamelCaseVariableNameRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::PHP_73);

    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [__DIR__ . '/app', __DIR__ . '/system', __DIR__ . '/tests', __DIR__ . '/utils/Rector']);

    // do you need to include constants, class aliases or custom autoloader? files listed will be executed
    $parameters->set(Option::BOOTSTRAP_FILES, [
        __DIR__ . '/system/Test/bootstrap.php',
    ]);

    // is there a file you need to skip?
    $parameters->set(Option::SKIP, [
        __DIR__ . '/app/Views',
        __DIR__ . '/system/Debug/Toolbar/Views/toolbar.tpl.php',
        __DIR__ . '/system/ThirdParty',
        __DIR__ . '/tests/system/Config/fixtures',
        __DIR__ . '/tests/_support',
        JsonThrowOnErrorRector::class,
        StringifyStrNeedlesRector::class,

        // requires php 8
        RemoveUnusedPromotedPropertyRector::class,

        // currently buggy on call inside assign, wait for next release
        RemoveParentCallWithoutParentRector::class,

        // private method called via getPrivateMethodInvoker
        RemoveUnusedPrivateMethodRector::class => [
            __DIR__ . '/system/Entity/Entity.php',
            __DIR__ . '/tests/system/Test/ReflectionHelperTest.php',
        ],

        // call on purpose for nothing happen check
        RemoveEmptyMethodCallRector::class => [
            __DIR__ . '/tests',
        ],

        // currently buggy on class implements ArrayAccess, wait for next release
        RemoveDeadStmtRector::class => [
            __DIR__ . '/tests/system/Cookie/CookieTest.php',
        ],

        // check on constant compare
        UnwrapFutureCompatibleIfPhpVersionRector::class => [
            __DIR__ . '/system/CodeIgniter.php',
        ],

        // check context ResponseTrait
        RemoveUselessReturnTagRector::class => [
            __DIR__ . '/system/HTTP/MessageTrait.php',
        ],

        // casted to Entity via EntityTest->getCastEntity()
        RecastingRemovalRector::class => [
            __DIR__ . '/tests/system/Entity/EntityTest.php',
        ],

        // session handlers have the gc() method with underscored parameter `$max_lifetime`
        UnderscoreToCamelCaseVariableNameRector::class => [
            __DIR__ . '/system/Session/Handlers',
        ],
    ]);

    // auto import fully qualified class names
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);

    $services = $containerConfigurator->services();
    $services->load('Symplify\\PackageBuilder\\', __DIR__ . '/vendor/symplify/package-builder/src');

    $services->set(UnderscoreToCamelCaseVariableNameRector::class);
    $services->set(SimplifyUselessVariableRector::class);
    $services->set(RemoveAlwaysElseRector::class);
    $services->set(PassStrictParameterToFunctionParameterRector::class);
    $services->set(CountArrayToEmptyArrayComparisonRector::class);
    $services->set(ForToForeachRector::class);
    $services->set(ChangeNestedForeachIfsToEarlyContinueRector::class);
    $services->set(ChangeIfElseValueAssignToEarlyReturnRector::class);
    $services->set(SimplifyStrposLowerRector::class);
    $services->set(CombineIfRector::class);
    $services->set(SimplifyIfReturnBoolRector::class);
    $services->set(InlineIfToExplicitIfRector::class);
    $services->set(PreparedValueToEarlyReturnRector::class);
    $services->set(ShortenElseIfRector::class);
    $services->set(SimplifyIfElseToTernaryRector::class);
    $services->set(UnusedForeachValueToArrayKeysRector::class);
    $services->set(ChangeArrayPushToArrayAssignRector::class);
    $services->set(UnnecessaryTernaryExpressionRector::class);
    $services->set(RemoveErrorSuppressInTryCatchStmtsRector::class);
    $services->set(TernaryToNullCoalescingRector::class);
    $services->set(ListToArrayDestructRector::class);
    $services->set(RemoveVarTagFromClassConstantRector::class);
    $services->set(AddPregQuoteDelimiterRector::class);
    $services->set(SimplifyRegexPatternRector::class);
    $services->set(RemoveExtraParametersRector::class);
    $services->set(FuncGetArgsToVariadicParamRector::class);
    $services->set(MakeInheritedMethodVisibilitySameAsParentRector::class);
};
