<?php

use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Utils\Rector\PassStrictParameterToFunctionParameterRector;
use Utils\Rector\UnderscoreToCamelCaseVariableNameRector;

return static function (ContainerConfigurator $containerConfigurator): void {
	$parameters = $containerConfigurator->parameters();

	// paths to refactor; solid alternative to CLI arguments
	$parameters->set(Option::PATHS, [__DIR__ . '/app', __DIR__ . '/system', __DIR__ . '/tests']);

	// is there a file you need to skip?
	$parameters->set(Option::SKIP, [
		__DIR__ . '/app/Views',
		__DIR__ . '/system/Debug/Toolbar/Views/toolbar.tpl.php',
		__DIR__ . '/system/ThirdParty',
		__DIR__ . '/tests/system/Config/fixtures',
		__DIR__ . '/tests/system/Models',
		__DIR__ . '/tests/_support',
		PassStrictParameterToFunctionParameterRector::class => [__DIR__ . '/tests/system/Database/Live/SelectTest.php'],
	]);

	// Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
	$parameters->set(Option::AUTOLOAD_PATHS, [
		// autoload specific file
		__DIR__ . '/system/Test/bootstrap.php',
	]);

	// auto import fully qualified class names
	$parameters->set(Option::AUTO_IMPORT_NAMES, true);
	$parameters->set(Option::ENABLE_CACHE, true);
	$parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);
	$parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/phpstan.neon.dist');

	$services = $containerConfigurator->services();
	$services->set(UnderscoreToCamelCaseVariableNameRector::class);
	$services->set(SimplifyUselessVariableRector::class);
	$services->set(RemoveAlwaysElseRector::class);
	$services->set(PassStrictParameterToFunctionParameterRector::class);
	$services->set(CountArrayToEmptyArrayComparisonRector::class);
	$services->set(ForToForeachRector::class);
	$services->set(ChangeNestedForeachIfsToEarlyContinueRector::class);
	$services->set(ChangeIfElseValueAssignToEarlyReturnRector::class);
	$services->set(ArrayKeyFirstLastRector::class);
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
};
