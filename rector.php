<?php

use Rector\Core\Configuration\Option;
use Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector;
use Rector\Performance\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\SOLID\Rector\If_\RemoveAlwaysElseRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Utils\Rector\PassStrictParameterToFunctionParameterRector;
use Utils\Rector\UnderscoreToCamelCaseVariableNameRector;

return static function (ContainerConfigurator $containerConfigurator): void {
	$parameters = $containerConfigurator->parameters();

	// paths to refactor; solid alternative to CLI arguments
	$parameters->set(Option::PATHS, [__DIR__ . '/app', __DIR__ . '/system']);

	// is there a file you need to skip?
	$parameters->set(Option::EXCLUDE_PATHS, [
		__DIR__ . '/app/Views',
		__DIR__ . '/system/Debug/Toolbar/Views/toolbar.tpl.php',
		__DIR__ . '/system/ThirdParty',
	]);

	// Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
	$parameters->set(Option::AUTOLOAD_PATHS, [
		// autoload specific file
		__DIR__ . '/system/Test/bootstrap.php',
	]);

	// auto import fully qualified class names
	$parameters->set(Option::AUTO_IMPORT_NAMES, true);

	$parameters->set(Option::SKIP, [
		// skipped for UnderscoreToCamelCaseVariableNameRector rule
		// as the underscored variable removed in 4.1 branch
		UnderscoreToCamelCaseVariableNameRector::class => [__DIR__ . '/system/Autoloader/Autoloader.php'],
	]);

	$parameters->set(Option::ENABLE_CACHE, true);
	$parameters->set(Option::PHP_VERSION_FEATURES, '7.2');

	$services = $containerConfigurator->services();
	$services->set(UnderscoreToCamelCaseVariableNameRector::class);
	$services->set(SimplifyUselessVariableRector::class);
	$services->set(RemoveAlwaysElseRector::class);
	$services->set(PassStrictParameterToFunctionParameterRector::class);
	$services->set(CountArrayToEmptyArrayComparisonRector::class);
};
