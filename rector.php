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
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
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
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
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
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_100,
    ]);

    $rectorConfig->parallel();

    // paths to refactor; solid alternative to CLI arguments
    $rectorConfig->paths([__DIR__ . '/app', __DIR__ . '/system', __DIR__ . '/tests', __DIR__ . '/utils']);

    // do you need to include constants, class aliases or custom autoloader? files listed will be executed
    $rectorConfig->bootstrapFiles([
        __DIR__ . '/system/Test/bootstrap.php',
    ]);

    $rectorConfig->phpstanConfigs([
        __DIR__ . '/phpstan.neon.dist',
        __DIR__ . '/vendor/codeigniter/phpstan-codeigniter/extension.neon',
        __DIR__ . '/vendor/phpstan/phpstan-strict-rules/rules.neon',
    ]);

    // is there a file you need to skip?
    $rectorConfig->skip([
        __DIR__ . '/system/Debug/Toolbar/Views/toolbar.tpl.php',
        __DIR__ . '/system/ThirdParty',
        __DIR__ . '/tests/system/Config/fixtures',
        __DIR__ . '/tests/system/Filters/fixtures',
        __DIR__ . '/tests/_support',
        JsonThrowOnErrorRector::class,
        YieldDataProviderRector::class,

        RemoveUnusedPrivateMethodRector::class => [
            // private method called via getPrivateMethodInvoker
            __DIR__ . '/tests/system/Test/ReflectionHelperTest.php',
        ],

        RemoveUnusedConstructorParamRector::class => [
            // there are deprecated parameters
            __DIR__ . '/system/Debug/Exceptions.php',
            // @TODO remove if deprecated $httpVerb is removed
            __DIR__ . '/system/Router/AutoRouterImproved.php',
            // @TODO remove if deprecated $config is removed
            __DIR__ . '/system/HTTP/Request.php',
        ],

        // check on constant compare
        UnwrapFutureCompatibleIfPhpVersionRector::class => [
            __DIR__ . '/system/Autoloader/Autoloader.php',
        ],

        // session handlers have the gc() method with underscored parameter `$max_lifetime`
        UnderscoreToCamelCaseVariableNameRector::class => [
            __DIR__ . '/system/Session/Handlers',
        ],

        // use mt_rand instead of random_int on purpose on non-cryptographically random
        RandomFunctionRector::class,

        SimplifyRegexPatternRector::class,
    ]);

    // auto import fully qualified class names
    $rectorConfig->importNames();

    $rectorConfig->rule(UnderscoreToCamelCaseVariableNameRector::class);
    $rectorConfig->rule(SimplifyUselessVariableRector::class);
    $rectorConfig->rule(RemoveAlwaysElseRector::class);
    $rectorConfig->rule(PassStrictParameterToFunctionParameterRector::class);
    $rectorConfig->rule(CountArrayToEmptyArrayComparisonRector::class);
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
    $rectorConfig->rule(SimplifyRegexPatternRector::class);
    $rectorConfig->rule(FuncGetArgsToVariadicParamRector::class);
    $rectorConfig->rule(MakeInheritedMethodVisibilitySameAsParentRector::class);
    $rectorConfig->rule(SimplifyEmptyArrayCheckRector::class);
    $rectorConfig->rule(StringClassNameToClassConstantRector::class);
    $rectorConfig->rule(PrivatizeFinalClassPropertyRector::class);
    $rectorConfig->rule(CompleteDynamicPropertiesRector::class);
};
