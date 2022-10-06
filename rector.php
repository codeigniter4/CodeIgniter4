<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector;
use Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Utils\Rector\PassStrictParameterToFunctionParameterRector;
use Utils\Rector\RemoveErrorSuppressInTryCatchStmtsRector;
use Utils\Rector\RemoveVarTagFromClassConstantRector;
use Utils\Rector\UnderscoreToCamelCaseVariableNameRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_74,
        PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD,
        PHPUnitSetList::PHPUNIT_80,
        PHPUnitSetList::REMOVE_MOCKS,
    ]);

    $rectorConfig->parallel();

    // paths to refactor; solid alternative to CLI arguments
    $rectorConfig->paths([__DIR__ . '/app', __DIR__ . '/system', __DIR__ . '/tests', __DIR__ . '/utils']);

    // do you need to include constants, class aliases or custom autoloader? files listed will be executed
    $rectorConfig->bootstrapFiles([
        __DIR__ . '/system/Test/bootstrap.php',
    ]);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon.dist');

    // is there a file you need to skip?
    $rectorConfig->skip([
        __DIR__ . '/app/Views',
        __DIR__ . '/system/Debug/Toolbar/Views/toolbar.tpl.php',
        __DIR__ . '/system/ThirdParty',
        __DIR__ . '/tests/system/Config/fixtures',
        __DIR__ . '/tests/_support',
        JsonThrowOnErrorRector::class,
        StringifyStrNeedlesRector::class,

        RemoveUnusedPrivateMethodRector::class => [
            // private method called via getPrivateMethodInvoker
            __DIR__ . '/tests/system/Test/ReflectionHelperTest.php',
        ],

        // call on purpose for nothing happen check
        RemoveEmptyMethodCallRector::class => [
            __DIR__ . '/tests',
        ],

        // session handlers have the gc() method with underscored parameter `$max_lifetime`
        UnderscoreToCamelCaseVariableNameRector::class => [
            __DIR__ . '/system/Session/Handlers',
        ],

        StringClassNameToClassConstantRector::class => [
            // may cause load view files directly when detecting namespaced string
            // due to internal PHPStan issue
            __DIR__ . '/app/Config/Pager.php',
            __DIR__ . '/app/Config/Validation.php',
            __DIR__ . '/tests/system/Validation/StrictRules/ValidationTest.php',
            __DIR__ . '/tests/system/Validation/ValidationTest.php',
        ],

        // sometime too detail
        CountOnNullRector::class,

        // may not be unitialized on purpose
        AddDefaultValueForUndefinedVariableRector::class,

        // use mt_rand instead of random_int on purpose on non-cryptographically random
        RandomFunctionRector::class,

        // @TODO remove if https://github.com/rectorphp/rector-phpunit/issues/86 is fixed
        GetMockBuilderGetMockToCreateMockRector::class => [
            __DIR__ . '/tests/system/Email/EmailTest.php',
        ],
    ]);

    // auto import fully qualified class names
    $rectorConfig->importNames();

    $rectorConfig->rule(UnderscoreToCamelCaseVariableNameRector::class);
    $rectorConfig->rule(SimplifyUselessVariableRector::class);
    $rectorConfig->rule(RemoveAlwaysElseRector::class);
    $rectorConfig->rule(PassStrictParameterToFunctionParameterRector::class);
    $rectorConfig->rule(CountArrayToEmptyArrayComparisonRector::class);
    $rectorConfig->rule(ForToForeachRector::class);
    $rectorConfig->rule(ChangeNestedForeachIfsToEarlyContinueRector::class);
    $rectorConfig->rule(ChangeIfElseValueAssignToEarlyReturnRector::class);
    $rectorConfig->rule(SimplifyStrposLowerRector::class);
    $rectorConfig->rule(CombineIfRector::class);
    $rectorConfig->rule(SimplifyIfReturnBoolRector::class);
    $rectorConfig->rule(InlineIfToExplicitIfRector::class);
    $rectorConfig->rule(PreparedValueToEarlyReturnRector::class);
    $rectorConfig->rule(ShortenElseIfRector::class);
    $rectorConfig->rule(SimplifyIfElseToTernaryRector::class);
    $rectorConfig->rule(UnusedForeachValueToArrayKeysRector::class);
    $rectorConfig->rule(ChangeArrayPushToArrayAssignRector::class);
    $rectorConfig->rule(UnnecessaryTernaryExpressionRector::class);
    $rectorConfig->rule(RemoveErrorSuppressInTryCatchStmtsRector::class);
    $rectorConfig->rule(RemoveVarTagFromClassConstantRector::class);
    $rectorConfig->rule(AddPregQuoteDelimiterRector::class);
    $rectorConfig->rule(SimplifyRegexPatternRector::class);
    $rectorConfig->rule(FuncGetArgsToVariadicParamRector::class);
    $rectorConfig->rule(MakeInheritedMethodVisibilitySameAsParentRector::class);
    $rectorConfig->rule(SimplifyEmptyArrayCheckRector::class);
    $rectorConfig->rule(NormalizeNamespaceByPSR4ComposerAutoloadRector::class);
    $rectorConfig->rule(StringClassNameToClassConstantRector::class);
    $rectorConfig->rule(PrivatizeFinalClassPropertyRector::class);
    $rectorConfig->rule(CompleteDynamicPropertiesRector::class);
};
