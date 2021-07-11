<?php

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
use Rector\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
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
        PassStrictParameterToFunctionParameterRector::class => [__DIR__ . '/tests/system/Database/Live/SelectTest.php'],
        JsonThrowOnErrorRector::class,
        StringifyStrNeedlesRector::class,
        InlineIfToExplicitIfRector::class => [
            __DIR__ . '/app/Config',
            __DIR__ . '/system/Test/bootstrap.php',
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
    $services->set(RemoveDuplicatedCaseInSwitchRector::class);
    $services->set(InlineIfToExplicitIfRector::class);
    $services->set(PreparedValueToEarlyReturnRector::class);
    $services->set(ShortenElseIfRector::class);
    $services->set(RemoveUnusedForeachKeyRector::class);
    $services->set(SimplifyIfElseToTernaryRector::class);
    $services->set(UnusedForeachValueToArrayKeysRector::class);
    $services->set(RemoveConcatAutocastRector::class);
    $services->set(ChangeArrayPushToArrayAssignRector::class);
    $services->set(UnnecessaryTernaryExpressionRector::class);
    $services->set(RemoveUnusedPrivatePropertyRector::class);
    $services->set(RemoveErrorSuppressInTryCatchStmtsRector::class);
    $services->set(TernaryToNullCoalescingRector::class);
    $services->set(ListToArrayDestructRector::class);
    $services->set(MoveVariableDeclarationNearReferenceRector::class);
    $services->set(RemoveVarTagFromClassConstantRector::class);
    $services->set(AddPregQuoteDelimiterRector::class);
    $services->set(SimplifyRegexPatternRector::class);
    $services->set(RemoveExtraParametersRector::class);
    $services->set(RemoveUnusedVariableAssignRector::class);
};
