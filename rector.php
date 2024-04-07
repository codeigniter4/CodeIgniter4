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
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Ternary\TernaryEmptyArrayArrayDimFetchToCoalesceRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Utils\Rector\PassStrictParameterToFunctionParameterRector;
use Utils\Rector\RemoveErrorSuppressInTryCatchStmtsRector;
use Utils\Rector\UnderscoreToCamelCaseVariableNameRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_81,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_100,
    ]);

    $rectorConfig->parallel(120, 8, 10);

    // Github action cache
    $rectorConfig->cacheClass(FileCacheStorage::class);
    if (is_dir('/tmp')) {
        $rectorConfig->cacheDirectory('/tmp/rector');
    }

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
        __DIR__ . '/tests/_support/Commands/Foobar.php',
        __DIR__ . '/tests/_support/View',

        JsonThrowOnErrorRector::class,
        YieldDataProviderRector::class,

        RemoveUnusedPromotedPropertyRector::class => [
            // Bug in rector 1.0.0. See https://github.com/rectorphp/rector-src/pull/5573
            __DIR__ . '/tests/_support/Entity/CustomUser.php',
        ],

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

        SimplifyRegexPatternRector::class,

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

        // PHP 8.1 features but cause breaking changes
        FinalizePublicClassConstantRector::class => [
            __DIR__ . '/system/Cache/Handlers/BaseHandler.php',
            __DIR__ . '/system/Cache/Handlers/FileHandler.php',
            __DIR__ . '/system/CodeIgniter.php',
            __DIR__ . '/system/Events/Events.php',
            __DIR__ . '/system/Log/Handlers/ChromeLoggerHandler.php',
            __DIR__ . '/system/Log/Handlers/ErrorlogHandler.php',
            __DIR__ . '/system/Security/Security.php',
        ],
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

        // PHPUnit 10 (requires PHP 8.1) features
        DataProviderAnnotationToAttributeRector::class,
        DependsAnnotationWithValueToAttributeRector::class,
        AnnotationWithValueToAttributeRector::class,
        AnnotationToAttributeRector::class,
        CoversAnnotationWithValueToAttributeRector::class,
    ]);

    // auto import fully qualified class names
    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();

    $rectorConfig->rule(DeclareStrictTypesRector::class);
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
    $rectorConfig->rule(SimplifyRegexPatternRector::class);
    $rectorConfig->rule(FuncGetArgsToVariadicParamRector::class);
    $rectorConfig->rule(MakeInheritedMethodVisibilitySameAsParentRector::class);
    $rectorConfig->rule(SimplifyEmptyArrayCheckRector::class);
    $rectorConfig->rule(SimplifyEmptyCheckOnEmptyArrayRector::class);
    $rectorConfig->rule(TernaryEmptyArrayArrayDimFetchToCoalesceRector::class);
    $rectorConfig->rule(EmptyOnNullableObjectToInstanceOfRector::class);
    $rectorConfig->rule(DisallowedEmptyRuleFixerRector::class);
    $rectorConfig->rule(PrivatizeFinalClassPropertyRector::class);
    $rectorConfig->rule(CompleteDynamicPropertiesRector::class);
    $rectorConfig->rule(BooleanInIfConditionRuleFixerRector::class);
    $rectorConfig->rule(SingleInArrayToCompareRector::class);
    $rectorConfig->rule(VersionCompareFuncCallToConstantRector::class);
    $rectorConfig->rule(ExplicitBoolCompareRector::class);

    $rectorConfig
        ->ruleWithConfiguration(StringClassNameToClassConstantRector::class, [
            // keep '\\' prefix string on string '\Foo\Bar'
            StringClassNameToClassConstantRector::SHOULD_KEEP_PRE_SLASH => true,
        ]);
};
