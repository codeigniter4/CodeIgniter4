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

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\Ternary\TernaryEmptyArrayArrayDimFetchToCoalesceRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
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
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Rector\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Utils\Rector\PassStrictParameterToFunctionParameterRector;
use Utils\Rector\RemoveErrorSuppressInTryCatchStmtsRector;
use Utils\Rector\UnderscoreToCamelCaseVariableNameRector;

return RectorConfig::configure()
    ->withPhpSets(php81: true)
    ->withPreparedSets(deadCode: true)
    ->withSets([
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_100,
    ])
    ->withParallel(120, 8, 10)
    ->withCache(
        // Github action cache or local
        is_dir('/tmp') ? '/tmp/rector' : null,
        FileCacheStorage::class
    )
    // paths to refactor; solid alternative to CLI arguments
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/system',
        __DIR__ . '/tests',
        __DIR__ . '/utils/src',
    ])
    // do you need to include constants, class aliases or custom autoloader? files listed will be executed
    ->withBootstrapFiles([
        __DIR__ . '/system/Test/bootstrap.php',
    ])
    ->withPHPStanConfigs([
        __DIR__ . '/phpstan.neon.dist',
        __DIR__ . '/vendor/codeigniter/phpstan-codeigniter/extension.neon',
        __DIR__ . '/vendor/phpstan/phpstan-strict-rules/rules.neon',
    ])
    // is there a file you need to skip?
    ->withSkip([
        __DIR__ . '/system/Debug/Toolbar/Views/toolbar.tpl.php',
        __DIR__ . '/system/ThirdParty',
        __DIR__ . '/tests/system/Config/fixtures',
        __DIR__ . '/tests/system/Filters/fixtures',
        __DIR__ . '/tests/_support/Commands/Foobar.php',
        __DIR__ . '/tests/_support/View',
        __DIR__ . '/tests/system/View/Views',

        YieldDataProviderRector::class,

        RemoveUnusedPrivateMethodRector::class => [
            // private method called via getPrivateMethodInvoker
            __DIR__ . '/tests/system/Test/ReflectionHelperTest.php',
            __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
        ],

        RemoveUnusedConstructorParamRector::class => [
            // there are deprecated parameters
            __DIR__ . '/system/Debug/Exceptions.php',
            // @TODO remove if deprecated $httpVerb is removed
            __DIR__ . '/system/Router/AutoRouterImproved.php',
            // @TODO remove if deprecated $config is removed
            __DIR__ . '/system/HTTP/Request.php',
            __DIR__ . '/system/HTTP/Response.php',
        ],

        // check on constant compare
        UnwrapFutureCompatibleIfPhpVersionRector::class => [
            __DIR__ . '/system/Autoloader/Autoloader.php',
        ],

        UnderscoreToCamelCaseVariableNameRector::class => [
            // session handlers have the gc() method with underscored parameter `$max_lifetime`
            __DIR__ . '/system/Session/Handlers',
            __DIR__ . '/tests/_support/Entity/CustomUser.php',
        ],

        DeclareStrictTypesRector::class => [
            __DIR__ . '/app',
            __DIR__ . '/system/CodeIgniter.php',
            __DIR__ . '/system/Config/BaseConfig.php',
            __DIR__ . '/system/Commands/Generators/Views',
            __DIR__ . '/system/Pager/Views',
            __DIR__ . '/system/Test/ControllerTestTrait.php',
            __DIR__ . '/system/Validation/Views',
            __DIR__ . '/system/View/Parser.php',
            __DIR__ . '/tests/system/Debug/ExceptionsTest.php',
            __DIR__ . '/tests/system/View/Views',
        ],

        // use mt_rand instead of random_int on purpose on non-cryptographically random
        RandomFunctionRector::class,

        // PHP 8.0 features but cause breaking changes
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__ . '/system/Database/BaseResult.php',
            __DIR__ . '/system/Database/RawSql.php',
            __DIR__ . '/system/Debug/BaseExceptionHandler.php',
            __DIR__ . '/system/Filters/Filters.php',
            __DIR__ . '/system/HTTP/CURLRequest.php',
            __DIR__ . '/system/HTTP/DownloadResponse.php',
            __DIR__ . '/system/HTTP/IncomingRequest.php',
            __DIR__ . '/system/Security/Security.php',
            __DIR__ . '/system/Session/Session.php',
        ],
        MixedTypeRector::class,

        ReturnNeverTypeRector::class => [
            __DIR__ . '/system/Cache/Handlers/BaseHandler.php',
            __DIR__ . '/system/Cache/Handlers/MemcachedHandler.php',
            __DIR__ . '/system/Cache/Handlers/WincacheHandler.php',
            __DIR__ . '/system/CodeIgniter.php',
            __DIR__ . '/system/Database/MySQLi/Utils.php',
            __DIR__ . '/system/Database/OCI8/Utils.php',
            __DIR__ . '/system/Database/Postgre/Utils.php',
            __DIR__ . '/system/Database/SQLSRV/Utils.php',
            __DIR__ . '/system/Database/SQLite3/Utils.php',
            __DIR__ . '/system/HTTP/DownloadResponse.php',
            __DIR__ . '/system/HTTP/SiteURI.php',
            __DIR__ . '/system/Helpers/kint_helper.php',
            __DIR__ . '/tests/_support/Autoloader/FatalLocator.php',
        ],

        // Unnecessary (string) is inserted
        NullToStrictStringFuncCallArgRector::class,
    ])
    // auto import fully qualified class names
    ->withImportNames(removeUnusedImports: true)
    ->withRules([
        DeclareStrictTypesRector::class,
        UnderscoreToCamelCaseVariableNameRector::class,
        SimplifyUselessVariableRector::class,
        RemoveAlwaysElseRector::class,
        PassStrictParameterToFunctionParameterRector::class,
        CountArrayToEmptyArrayComparisonRector::class,
        ChangeNestedForeachIfsToEarlyContinueRector::class,
        ChangeIfElseValueAssignToEarlyReturnRector::class,
        InlineIfToExplicitIfRector::class,
        PreparedValueToEarlyReturnRector::class,
        ShortenElseIfRector::class,
        UnusedForeachValueToArrayKeysRector::class,
        ChangeArrayPushToArrayAssignRector::class,
        RemoveErrorSuppressInTryCatchStmtsRector::class,
        FuncGetArgsToVariadicParamRector::class,
        MakeInheritedMethodVisibilitySameAsParentRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        TernaryEmptyArrayArrayDimFetchToCoalesceRector::class,
        EmptyOnNullableObjectToInstanceOfRector::class,
        DisallowedEmptyRuleFixerRector::class,
        PrivatizeFinalClassPropertyRector::class,
        CompleteDynamicPropertiesRector::class,
        BooleanInIfConditionRuleFixerRector::class,
        VersionCompareFuncCallToConstantRector::class,
        AddClosureVoidReturnTypeWhereNoReturnRector::class,
        AddFunctionVoidReturnTypeWhereNoReturnRector::class,
        AddMethodCallBasedStrictParamTypeRector::class,
        TypedPropertyFromAssignsRector::class,
        ClosureReturnTypeRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
    ])
    ->withConfiguredRule(StringClassNameToClassConstantRector::class, [
        // keep '\\' prefix string on string '\Foo\Bar'
        StringClassNameToClassConstantRector::SHOULD_KEEP_PRE_SLASH => true,
    ])
    ->withCodeQualityLevel(24);
