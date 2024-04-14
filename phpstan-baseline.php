<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type array\\<string, array\\<int, string\\>\\> of property Config\\\\Filters\\:\\:\\$methods is not the same as PHPDoc type array of overridden property CodeIgniter\\\\Config\\\\Filters\\:\\:\\$methods\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Config/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type array\\<string, array\\<string, array\\<int, string\\>\\>\\> of property Config\\\\Filters\\:\\:\\$filters is not the same as PHPDoc type array of overridden property CodeIgniter\\\\Config\\\\Filters\\:\\:\\$filters\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Config/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function method_exists\\(\\) with \'Composer\\\\\\\\InstalledVersions\' and \'getAllRawData\' will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/Autoloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Autoloader\\\\Autoloader\\:\\:loadComposerNamespaces\\(\\) has parameter \\$composerPackages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/Autoloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Config\\\\Autoload\\:\\:\\$helpers \\(array\\<int, string\\>\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/Autoloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\|string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/FileLocator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/Autoloader/FileLocator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Autoloader\\\\FileLocatorCached\\:\\:search\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/FileLocatorCached.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Autoloader\\\\FileLocatorCached\\:\\:\\$cache type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/FileLocatorCached.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Autoloader\\\\FileLocatorInterface\\:\\:search\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/FileLocatorInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:__call\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:__get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:chunk\\(\\) has parameter \\$userFunc with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:cleanValidationRules\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:cleanValidationRules\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:convertToReturnType\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:delete\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doDelete\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doErrors\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doFind\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doFind\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doFindAll\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doFindColumn\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doFirst\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doInsertBatch\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doProtectFields\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doProtectFieldsForInsert\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doUpdate\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doUpdateBatch\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:find\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:findAll\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:findColumn\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:first\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:getIdValue\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:getValidationMessages\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:getValidationRules\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:getValidationRules\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:paginate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:setAllowedFields\\(\\) has parameter \\$allowedFields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:setCreatedField\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:setUpdatedField\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:setValidationMessage\\(\\) has parameter \\$fieldMessages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:setValidationMessages\\(\\) has parameter \\$validationMessages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:setValidationRule\\(\\) has parameter \\$fieldRules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:transformDataToArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:trigger\\(\\) has parameter \\$eventData with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:trigger\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:update\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array\\<string, string\\> given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array\\|int\\|string\\|null given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, array\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\!\\=\\= between mixed and null will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'ANSICON\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'NO_COLOR\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'argv\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:getOptions\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:getSegments\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:isZeroOptions\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:printKeysAndValues\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:prompt\\(\\) has parameter \\$validation with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:promptByKey\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:promptByKey\\(\\) has parameter \\$text with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:promptByKey\\(\\) has parameter \\$validation with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:promptByMultipleKeys\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:promptByMultipleKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:table\\(\\) has parameter \\$tbody with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:table\\(\\) has parameter \\$thead with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:validate\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array\\<int, string\\> given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array\\|string\\|null given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in \\|\\|, string given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in \\|\\|, string\\|null given on the left side\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in \\|\\|, string\\|null given on the right side\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\CLI\\\\CLI\\:\\:\\$options type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\CLI\\\\CLI\\:\\:\\$segments type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Commands\\:\\:getCommandAlternatives\\(\\) has parameter \\$collection with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Commands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Commands\\:\\:getCommandAlternatives\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Commands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Commands\\:\\:getCommands\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Commands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Commands\\:\\:run\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Commands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Commands\\:\\:verifyCommand\\(\\) has parameter \\$commands with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Commands.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\CLI\\\\Commands\\:\\:\\$commands type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Commands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Console\\:\\:parseParamsForHelpOption\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Console.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Console\\:\\:parseParamsForHelpOption\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Console.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\CacheInterface\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/CacheInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\CacheInterface\\:\\:getCacheInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/CacheInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\CacheInterface\\:\\:getMetaData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/CacheInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\CacheInterface\\:\\:save\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/CacheInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\FactoriesCache\\\\FileVarExportHandler\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/FactoriesCache/FileVarExportHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\FactoriesCache\\\\FileVarExportHandler\\:\\:save\\(\\) has parameter \\$val with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/FactoriesCache/FileVarExportHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\BaseHandler\\:\\:remember\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\DummyHandler\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/DummyHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\DummyHandler\\:\\:getCacheInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/DummyHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\DummyHandler\\:\\:getMetaData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/DummyHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\DummyHandler\\:\\:remember\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/DummyHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\DummyHandler\\:\\:save\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/DummyHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function property_exists\\(\\) with Config\\\\Cache and \'file\' will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:getCacheInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:getDirFileInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:getFileInfo\\(\\) has parameter \\$returnedValues with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:getFileInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:getMetaData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:save\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$result might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\MemcachedHandler\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\MemcachedHandler\\:\\:getCacheInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\MemcachedHandler\\:\\:getMetaData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\MemcachedHandler\\:\\:save\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Cache\\\\Handlers\\\\MemcachedHandler\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\PredisHandler\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\PredisHandler\\:\\:getCacheInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\PredisHandler\\:\\:getMetaData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\PredisHandler\\:\\:save\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Cache\\\\Handlers\\\\PredisHandler\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\RedisHandler\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\RedisHandler\\:\\:getCacheInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\RedisHandler\\:\\:getMetaData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\RedisHandler\\:\\:save\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Cache\\\\Handlers\\\\RedisHandler\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\WincacheHandler\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/WincacheHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\WincacheHandler\\:\\:getCacheInfo\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/WincacheHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\WincacheHandler\\:\\:getMetaData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/WincacheHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\WincacheHandler\\:\\:save\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/WincacheHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:getPerformanceStats\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/CreateDatabase.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/MigrateStatus.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$params \\(array\\<string, mixed\\>\\) of method CodeIgniter\\\\Commands\\\\Database\\\\MigrateStatus\\:\\:run\\(\\) should be contravariant with parameter \\$params \\(array\\<int\\|string, string\\|null\\>\\) of method CodeIgniter\\\\CLI\\\\BaseCommand\\:\\:run\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/MigrateStatus.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/Seed.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Database\\\\ShowTableInfo\\:\\:makeTableRows\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/ShowTableInfo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Database\\\\ShowTableInfo\\:\\:makeTbodyForShowAllTables\\(\\) has parameter \\$tables with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/ShowTableInfo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Database\\\\ShowTableInfo\\:\\:makeTbodyForShowAllTables\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/ShowTableInfo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Database\\\\ShowTableInfo\\:\\:showAllTables\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/ShowTableInfo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Database\\\\ShowTableInfo\\:\\:showAllTables\\(\\) has parameter \\$tables with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/ShowTableInfo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Database\\\\ShowTableInfo\\:\\:showDataOfTable\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/ShowTableInfo.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'encryption\\.key\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Encryption/GenerateKey.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Encryption\\\\GenerateKey\\:\\:confirmOverwrite\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Encryption/GenerateKey.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Encryption\\\\GenerateKey\\:\\:setNewEncryptionKey\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Encryption/GenerateKey.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\TestGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/TestGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\TestGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/TestGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\TestGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/TestGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\TestGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/TestGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\TestGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/TestGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\TestGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/TestGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\TestGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/TestGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\TestGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/TestGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\ListCommands\\:\\:listFull\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/ListCommands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\ListCommands\\:\\:listFull\\(\\) has parameter \\$commands with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/ListCommands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\ListCommands\\:\\:listSimple\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/ListCommands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\ListCommands\\:\\:listSimple\\(\\) has parameter \\$commands with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/ListCommands.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, int given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Server/Serve.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinder\\:\\:arrayToTableRows\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Translation/LocalizationFinder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinder\\:\\:arrayToTableRows\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Translation/LocalizationFinder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinder\\:\\:buildMultiArray\\(\\) has parameter \\$fromKeys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Translation/LocalizationFinder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinder\\:\\:buildMultiArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Translation/LocalizationFinder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinder\\:\\:findTranslationsInFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Translation/LocalizationFinder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinder\\:\\:templateFile\\(\\) has parameter \\$language with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Translation/LocalizationFinder.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'CI_ENVIRONMENT\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Commands/Utilities/Environment.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\FilterCheck\\:\\:addRequiredFilters\\(\\) has parameter \\$filters with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/FilterCheck.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\FilterCheck\\:\\:addRequiredFilters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/FilterCheck.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Namespaces\\:\\:outputAllNamespaces\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Namespaces.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Namespaces\\:\\:outputAllNamespaces\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Namespaces.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Namespaces\\:\\:outputCINamespaces\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Namespaces.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Namespaces\\:\\:outputCINamespaces\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Namespaces.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Namespaces.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_HOST\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning non\\-falsy\\-string directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, Config\\\\Routing given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, string\\|null given\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes.php',
];
$ignoreErrors[] = [
	'message' => '#^Implicit array creation is not allowed \\- variable \\$filters might not exist\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/AutoRouteCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\AutoRouteCollector\\:\\:__construct\\(\\) has parameter \\$httpMethods with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/AutoRouteCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\AutoRouteCollector\\:\\:addFilters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/AutoRouteCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\AutoRouteCollector\\:\\:addFilters\\(\\) has parameter \\$routes with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/AutoRouteCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\AutoRouteCollector\\:\\:generateSampleUri\\(\\) has parameter \\$route with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/AutoRouteCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\ControllerMethodReader\\:\\:getParameters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/ControllerMethodReader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\ControllerMethodReader\\:\\:getRouteForDefaultController\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/ControllerMethodReader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\ControllerMethodReader\\:\\:read\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/ControllerMethodReader.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/ControllerMethodReader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\ControllerMethodReader\\:\\:getRouteWithoutController\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/ControllerMethodReader.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/ControllerMethodReader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\FilterFinder\\:\\:getRouteFilters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/FilterFinder.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'REMOTE_ADDR\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'REQUEST_METHOD\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset string directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function cache\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function class_uses_recursive\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function cookie\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function db_connect\\(\\) has parameter \\$db with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function esc\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function esc\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function helper\\(\\) has parameter \\$filenames with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function lang\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function log_message\\(\\) has parameter \\$context with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function old\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function service\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function session\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function single_service\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function stringify_attributes\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function trait_uses_recursive\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function view\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function view\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function view_cell\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string\\|null given on the left side\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\ComposerScripts\\:\\:postUpdate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/ComposerScripts.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'CI_ENVIRONMENT\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Config/AutoloadConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset non\\-falsy\\-string directly on \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Config/BaseConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\BaseConfig\\:\\:__set_state\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\BaseConfig\\:\\:initEnvValue\\(\\) has parameter \\$property with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\BaseConfig\\:\\:\\$registrars type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Config/BaseService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\BaseService\\:\\:__callStatic\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\BaseService\\:\\:getSharedInstance\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseService.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\BaseService\\:\\:\\$services type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseService.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset string directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Config/DotEnv.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset string of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/DotEnv.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Config/DotEnv.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\DotEnv\\:\\:normaliseVariable\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/DotEnv.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\DotEnv\\:\\:parse\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/DotEnv.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:__callStatic\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:createInstance\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:getComponentInstances\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:getDefinedInstance\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:getDefinedInstance\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:locateClass\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:setComponentInstances\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:setOptions\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:verifyInstanceOf\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:verifyPreferApp\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, string\\|null given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\Factory\\:\\:\\$default type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factory.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\Factory\\:\\:\\$models type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factory.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\Filters\\:\\:\\$filters type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\Filters\\:\\:\\$methods type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SERVER_PROTOCOL\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument \\#1 \\$name \\(\'Config\\\\\\\\Modules\'\\) passed to function config does not extend CodeIgniter\\\\\\\\Config\\\\\\\\BaseConfig\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Services\\:\\:curlrequest\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Services\\:\\:email\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Services\\:\\:superglobals\\(\\) has parameter \\$get with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Services\\:\\:superglobals\\(\\) has parameter \\$server with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:setValidator\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:setValidator\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:validate\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:validate\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:validateData\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:validateData\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:validateData\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Controller\\:\\:\\$request \\(CodeIgniter\\\\HTTP\\\\CLIRequest\\|CodeIgniter\\\\HTTP\\\\IncomingRequest\\) does not accept CodeIgniter\\\\HTTP\\\\RequestInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$offset \\(string\\) of method CodeIgniter\\\\Cookie\\\\Cookie\\:\\:offsetSet\\(\\) should be contravariant with parameter \\$offset \\(string\\|null\\) of method ArrayAccess\\<string,bool\\|int\\|string\\>\\:\\:offsetSet\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cookie/Cookie.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Cookie/Cookie.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cookie\\\\CookieStore\\:\\:setCookie\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cookie/CookieStore.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cookie\\\\CookieStore\\:\\:setRawCookie\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cookie/CookieStore.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cookie\\\\CookieStore\\:\\:validateCookies\\(\\) has parameter \\$cookies with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cookie/CookieStore.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataCaster\\\\Cast\\\\ArrayCast\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/DataCaster/Cast/ArrayCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataCaster\\\\Cast\\\\CSVCast\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/DataCaster/Cast/CSVCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataCaster\\\\Cast\\\\JsonCast\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/DataCaster/Cast/JsonCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverter\\:\\:fromDataSource\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/DataConverter/DataConverter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverter\\:\\:toDataSource\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/DataConverter/DataConverter.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 30,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:__construct\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:__construct\\(\\) has parameter \\$tableName with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_deleteBatch\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_insert\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_insert\\(\\) has parameter \\$unescapedKeys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_like\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_replace\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_replace\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_update\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_whereIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_whereIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:addUnionStatement\\(\\) has parameter \\$union with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:batchObjectToArray\\(\\) has parameter \\$object with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:batchObjectToArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:buildSubquery\\(\\) has parameter \\$builder with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:delete\\(\\) has parameter \\$where with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:deleteBatch\\(\\) has parameter \\$constraints with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:deleteBatch\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:fieldsFromQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:formatValues\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:formatValues\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:from\\(\\) has parameter \\$from with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:getBinds\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:getCompiledQBWhere\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:getOperator\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:getSetData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:getWhere\\(\\) has parameter \\$where with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:groupBy\\(\\) has parameter \\$by with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:having\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:havingIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:havingIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:havingLike\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:havingNotIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:havingNotIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:insert\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:insertBatch\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:like\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:notHavingLike\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:notLike\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:objectToArray\\(\\) has parameter \\$object with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:objectToArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:onConstraint\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orHaving\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orHavingIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orHavingIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orHavingLike\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orHavingNotIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orHavingNotIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orLike\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orNotHavingLike\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orNotLike\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orWhere\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orWhereIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orWhereIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orWhereNotIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orWhereNotIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:replace\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:resetRun\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:resetRun\\(\\) has parameter \\$qbResetItems with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:resetSelect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:resetWrite\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:select\\(\\) has parameter \\$select with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:set\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:setData\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:setQueryAsData\\(\\) has parameter \\$columns with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:setUpdateBatch\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:trackAliases\\(\\) has parameter \\$table with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:union\\(\\) has parameter \\$union with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:unionAll\\(\\) has parameter \\$union with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:update\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:update\\(\\) has parameter \\$where with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:updateBatch\\(\\) has parameter \\$constraints with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:updateBatch\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:updateFields\\(\\) has parameter \\$ignore with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:upsert\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:upsertBatch\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:where\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:whereHaving\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:whereIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:whereIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:whereNotIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:whereNotIn\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, TWhenNot given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an elseif condition, \\(callable\\)\\|null given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, TWhen given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBFrom type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBGroupBy type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBHaving type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBJoin type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBNoEscape type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBOptions type has no value type specified in iterable type array\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBOrderBy type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBSelect type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$QBWhere type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$binds type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$bindsKeyCount type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$db \\(CodeIgniter\\\\Database\\\\BaseConnection\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$joinTypes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$randomKeyword type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$supportedIgnoreStatements type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\QueryInterface\\:\\:getOriginalQuery\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 13,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:__construct\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:__get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:_fieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:callFunction\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:close\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:escape\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:escape\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:escapeIdentifiers\\(\\) has parameter \\$item with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:escapeIdentifiers\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:foreignKeyDataToObjects\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:getForeignKeyData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:listTables\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:prepare\\(\\) has parameter \\$func with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:protectIdentifiers\\(\\) has parameter \\$item with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:protectIdentifiers\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:query\\(\\) has parameter \\$binds with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:setAliasedTables\\(\\) has parameter \\$aliases with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:table\\(\\) has parameter \\$tableName with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:transOff\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$aliasedTables type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$dataCache type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$encrypt type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$escapeChar type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$failover type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$pregEscapeChar type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$reservedIdentifiers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\{code\\: int\\|string\\|null, message\\: string\\|null\\}\\) of method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:error\\(\\) should be covariant with return type \\(array\\<string, int\\|string\\>\\) of method CodeIgniter\\\\Database\\\\ConnectionInterface\\<TConnection,TResult\\>\\:\\:error\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/BasePreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BasePreparedQuery\\:\\:_execute\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BasePreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BasePreparedQuery\\:\\:_prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BasePreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BasePreparedQuery\\:\\:execute\\(\\) has parameter \\$data with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BasePreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BasePreparedQuery\\:\\:prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BasePreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:fetchAssoc\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getCustomResultObject\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getFieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getFirstRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getLastRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getNextRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getPreviousRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getResult\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getResultArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getRowArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getUnbufferedRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:setRow\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseResult\\:\\:setRow\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$className \\(class\\-string\\) of method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getCustomResultObject\\(\\) should be contravariant with parameter \\$className \\(string\\) of method CodeIgniter\\\\Database\\\\ResultInterface\\<TConnection,TResult\\>\\:\\:getCustomResultObject\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseResult\\:\\:\\$customResultObject type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseResult\\:\\:\\$resultArray type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseResult\\:\\:\\$rowData type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Database/BaseUtils.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseUtils\\:\\:_backup\\(\\) has parameter \\$prefs with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseUtils.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseUtils\\:\\:backup\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseUtils.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseUtils\\:\\:getXMLFromResult\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseUtils.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseUtils\\:\\:listDatabases\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseUtils.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Config\\:\\:connect\\(\\) has parameter \\$group with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Config\\:\\:ensureFactory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Config\\:\\:forge\\(\\) has parameter \\$group with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Config\\:\\:getConnections\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Config\\:\\:utils\\(\\) has parameter \\$group with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Config\\:\\:\\$instances type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:callFunction\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ConnectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:callFunction\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ConnectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:escape\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ConnectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:escape\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ConnectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:query\\(\\) has parameter \\$binds with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ConnectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:table\\(\\) has parameter \\$tableName with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ConnectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Database\\:\\:initDriver\\(\\) has parameter \\$argument with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Database\\:\\:load\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Database\\:\\:parseDSN\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Database\\:\\:parseDSN\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Database\\:\\:\\$connections type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Exceptions\\\\DataException\\:\\:forEmptyInputGiven\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Exceptions/DataException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Exceptions\\\\DataException\\:\\:forFindColumnHaveMultipleColumns\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Exceptions/DataException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Exceptions\\\\DataException\\:\\:forInvalidAllowedFields\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Exceptions/DataException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Exceptions\\\\DataException\\:\\:forTableNotFound\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Exceptions/DataException.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 13,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_alterTable\\(\\) has parameter \\$processedFields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeDefault\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeDefault\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeDefault\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeType\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeUnique\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeUnique\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeUnique\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeUnsigned\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeUnsigned\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeUnsigned\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_createTable\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_createTableAttributes\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_processColumn\\(\\) has parameter \\$processedField with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_processFields\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_processForeignKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_processIndexes\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:addColumn\\(\\) has parameter \\$fields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:addField\\(\\) has parameter \\$fields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:addKey\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:addPrimaryKey\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:addUniqueKey\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:createTable\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:dropColumn\\(\\) has parameter \\$columnNames with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:modifyColumn\\(\\) has parameter \\$fields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:reset\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$fields \\(array\\<string, array\\|string\\>\\) does not accept array\\<int\\|string, int\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$fields type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$fkAllowActions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$foreignKeys type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$uniqueKeys type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$unsigned type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Migration\\:\\:down\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Migration\\:\\:up\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:__construct\\(\\) has parameter \\$db with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:addHistory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:clearHistory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:ensureTable\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:findMigrations\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:findNamespaceMigrations\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:force\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:getBatchHistory\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:getBatches\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:getCliMessages\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:getHistory\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:removeHistory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, int\\<0, max\\> given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, max\\> given\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:\\$cliMessages type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\MySQLi\\\\Builder\\:\\:\\$supportedIgnoreStatements type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 11,
	'path' => __DIR__ . '/system/Database/MySQLi/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Connection\\:\\:_close\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, int given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\MySQLi\\\\Connection\\:\\:\\$escapeChar is not the same as PHPDoc type array\\|string of overridden property CodeIgniter\\\\Database\\\\BaseConnection\\<mysqli,mysqli_result\\>\\:\\:\\$escapeChar\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\<string, int\\|string\\>\\) of method CodeIgniter\\\\Database\\\\MySQLi\\\\Connection\\:\\:error\\(\\) should be covariant with return type \\(array\\{code\\: int\\|string\\|null, message\\: string\\|null\\}\\) of method CodeIgniter\\\\Database\\\\BaseConnection\\<mysqli,mysqli_result\\>\\:\\:error\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Forge\\:\\:_alterTable\\(\\) has parameter \\$processedFields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Forge\\:\\:_createTableAttributes\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Forge\\:\\:_processColumn\\(\\) has parameter \\$processedField with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Forge\\:\\:_processIndexes\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\MySQLi\\\\Forge\\:\\:\\$createDatabaseStr is not the same as PHPDoc type string\\|false of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$createDatabaseStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\MySQLi\\\\Forge\\:\\:\\$_quoted_table_options type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\MySQLi\\\\Forge\\:\\:\\$_unsigned type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\<mysqli, mysqli_result\\>\\:\\:\\$mysqli\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/MySQLi/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\PreparedQuery\\:\\:_execute\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\PreparedQuery\\:\\:_prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mysqli_result\\|false\\) of method CodeIgniter\\\\Database\\\\MySQLi\\\\PreparedQuery\\:\\:_getResult\\(\\) should be covariant with return type \\(object\\|resource\\|null\\) of method CodeIgniter\\\\Database\\\\BasePreparedQuery\\<mysqli,mysqli_stmt,mysqli_result\\>\\:\\:_getResult\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Result\\:\\:fetchAssoc\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Result\\:\\:getFieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Result\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MySQLi\\\\Utils\\:\\:_backup\\(\\) has parameter \\$prefs with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\MySQLi\\\\Utils\\:\\:\\$listDatabases is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$listDatabases\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\MySQLi\\\\Utils\\:\\:\\$optimizeTable is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$optimizeTable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:_deleteBatch\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:_replace\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:_replace\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:_update\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:fieldsFromQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:resetSelect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\OCI8\\\\Connection of property CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:\\$randomKeyword type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:delete\\(\\) should be covariant with return type \\(bool\\|string\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:delete\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Connection\\:\\:bindParams\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Connection\\:\\:storedProcedure\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\OCI8\\\\Connection\\:\\:\\$escapeChar is not the same as PHPDoc type array\\|string of overridden property CodeIgniter\\\\Database\\\\BaseConnection\\<resource,resource\\>\\:\\:\\$escapeChar\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\OCI8\\\\Connection\\:\\:\\$reservedIdentifiers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\OCI8\\\\Connection\\:\\:\\$resetStmtId has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\OCI8\\\\Connection\\:\\:\\$validDSNs has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\{code\\: int\\|string\\|null, message\\: string\\|null\\}\\) of method CodeIgniter\\\\Database\\\\OCI8\\\\Connection\\:\\:error\\(\\) should be covariant with return type \\(array\\<string, int\\|string\\>\\) of method CodeIgniter\\\\Database\\\\ConnectionInterface\\<resource,resource\\>\\:\\:error\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:_alterTable\\(\\) has parameter \\$processedFields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:_attributeType\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:_processColumn\\(\\) has parameter \\$processedField with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type false of property CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:\\$createDatabaseStr is not the same as PHPDoc type string\\|false of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$createDatabaseStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type false of property CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:\\$createTableIfStr is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$createTableIfStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type false of property CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:\\$dropDatabaseStr is not the same as PHPDoc type string\\|false of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$dropDatabaseStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type false of property CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:\\$dropTableIfStr is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$dropTableIfStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:\\$renameTableStr is not the same as PHPDoc type string\\|false of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$renameTableStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:\\$fkAllowActions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\OCI8\\\\Forge\\:\\:\\$unsigned type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\PreparedQuery\\:\\:_execute\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\PreparedQuery\\:\\:_prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\OCI8\\\\Connection of property CodeIgniter\\\\Database\\\\OCI8\\\\PreparedQuery\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection\\<resource, resource\\> of overridden property CodeIgniter\\\\Database\\\\BasePreparedQuery\\<resource,resource,resource\\>\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Result\\:\\:fetchAssoc\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Result\\:\\:getFieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Result\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Utils\\:\\:_backup\\(\\) has parameter \\$prefs with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\OCI8\\\\Utils\\:\\:\\$listDatabases is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$listDatabases\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 7,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:_deleteBatch\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:_insert\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:_insert\\(\\) has parameter \\$unescapedKeys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:_update\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:replace\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:\\$randomKeyword type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:\\$supportedIgnoreStatements type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Database\\\\BaseBuilder\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:join\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Database\\\\BaseBuilder\\)\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:join\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Database\\\\BaseBuilder\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:orderBy\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Database\\\\BaseBuilder\\)\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orderBy\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:decrement\\(\\) should be covariant with return type \\(bool\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:decrement\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:delete\\(\\) should be covariant with return type \\(bool\\|string\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:delete\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:increment\\(\\) should be covariant with return type \\(bool\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:increment\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\Builder\\:\\:replace\\(\\) should be covariant with return type \\(CodeIgniter\\\\Database\\\\BaseResult\\|CodeIgniter\\\\Database\\\\Query\\|string\\|false\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:replace\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:_close\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:buildDSN\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:convertDSN\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:escape\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:escape\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:\\$escapeChar is not the same as PHPDoc type array\\|string of overridden property CodeIgniter\\\\Database\\\\BaseConnection\\<PgSql\\\\Connection,PgSql\\\\Result\\>\\:\\:\\$escapeChar\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:\\$connect_timeout has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:\\$options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:\\$service has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:\\$sslmode has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\<string, int\\|string\\>\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\Connection\\:\\:error\\(\\) should be covariant with return type \\(array\\{code\\: int\\|string\\|null, message\\: string\\|null\\}\\) of method CodeIgniter\\\\Database\\\\BaseConnection\\<PgSql\\\\Connection,PgSql\\\\Result\\>\\:\\:error\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_alterTable\\(\\) has parameter \\$processedFields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_attributeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_attributeType\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_createTableAttributes\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_processColumn\\(\\) has parameter \\$processedField with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\Postgre\\\\Connection of property CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:\\$_unsigned type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\PreparedQuery\\:\\:_execute\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\PreparedQuery\\:\\:_prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Result\\:\\:fetchAssoc\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Result\\:\\:getFieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Result\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Utils\\:\\:_backup\\(\\) has parameter \\$prefs with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\Postgre\\\\Utils\\:\\:\\$listDatabases is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$listDatabases\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\Postgre\\\\Utils\\:\\:\\$optimizeTable is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$optimizeTable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\PreparedQueryInterface\\:\\:execute\\(\\) has parameter \\$data with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/PreparedQueryInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\PreparedQueryInterface\\:\\:prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/PreparedQueryInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Query\\:\\:compileBinds\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Query\\:\\:matchNamedBinds\\(\\) has parameter \\$binds with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Query\\:\\:matchSimpleBinds\\(\\) has parameter \\$binds with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Query\\:\\:setBinds\\(\\) has parameter \\$binds with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Query\\:\\:\\$binds type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\QueryInterface\\:\\:setError\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/QueryInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:freeResult\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getCustomResultObject\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getFieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getFirstRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getLastRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getNextRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getPreviousRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getResult\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getResultArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getResultObject\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getRowArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:getUnbufferedRow\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:setRow\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ResultInterface\\:\\:setRow\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ResultInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$schema\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 9,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:_insert\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:_insert\\(\\) has parameter \\$unescapedKeys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:_replace\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:_replace\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:_update\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:fieldsFromQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:replace\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$selectOverride \\(bool\\) of method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:compileSelect\\(\\) should be contravariant with parameter \\$selectOverride \\(mixed\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:compileSelect\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:\\$randomKeyword type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Database\\\\BaseBuilder\\) of method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:maxMinAvgSum\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Database\\\\BaseBuilder\\)\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:maxMinAvgSum\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:delete\\(\\) should be covariant with return type \\(bool\\|string\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:delete\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:replace\\(\\) should be covariant with return type \\(CodeIgniter\\\\Database\\\\BaseResult\\|CodeIgniter\\\\Database\\\\Query\\|string\\|false\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:replace\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/Database/SQLSRV/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Connection\\:\\:__construct\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Connection\\:\\:_close\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLSRV\\\\Connection\\:\\:\\$escapeChar is not the same as PHPDoc type array\\|string of overridden property CodeIgniter\\\\Database\\\\BaseConnection\\<resource,resource\\>\\:\\:\\$escapeChar\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\<string, int\\|string\\>\\) of method CodeIgniter\\\\Database\\\\SQLSRV\\\\Connection\\:\\:error\\(\\) should be covariant with return type \\(array\\{code\\: int\\|string\\|null, message\\: string\\|null\\}\\) of method CodeIgniter\\\\Database\\\\BaseConnection\\<resource,resource\\>\\:\\:error\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$schema\\.$#',
	'count' => 13,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_alterTable\\(\\) has parameter \\$processedFields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_attributeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_attributeType\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_createTableAttributes\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_processColumn\\(\\) has parameter \\$processedField with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_processIndexes\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type array of property CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:\\$unsigned is not the same as PHPDoc type array\\|bool of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$unsigned\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:\\$createDatabaseStr is not the same as PHPDoc type string\\|false of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$createDatabaseStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:\\$createTableIfStr is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$createTableIfStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:\\$renameTableStr is not the same as PHPDoc type string\\|false of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$renameTableStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:\\$fkAllowActions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:\\$unsigned type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\PreparedQuery\\:\\:_execute\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\PreparedQuery\\:\\:_prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\PreparedQuery\\:\\:parameterize\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\SQLSRV\\\\Connection of property CodeIgniter\\\\Database\\\\SQLSRV\\\\PreparedQuery\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection\\<resource, resource\\> of overridden property CodeIgniter\\\\Database\\\\BasePreparedQuery\\<resource,resource,resource\\>\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLSRV\\\\PreparedQuery\\:\\:\\$parameters type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Result\\:\\:fetchAssoc\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Result\\:\\:getFieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Result\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Utils\\:\\:_backup\\(\\) has parameter \\$prefs with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLSRV\\\\Utils\\:\\:\\$listDatabases is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$listDatabases\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLSRV\\\\Utils\\:\\:\\$optimizeTable is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$optimizeTable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/SQLite3/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Builder\\:\\:_deleteBatch\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Builder\\:\\:_replace\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Builder\\:\\:_replace\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLite3\\\\Builder\\:\\:\\$randomKeyword type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLite3\\\\Builder\\:\\:\\$supportedIgnoreStatements type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Connection\\:\\:_close\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Connection\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLite3\\\\Connection\\:\\:\\$escapeChar is not the same as PHPDoc type array\\|string of overridden property CodeIgniter\\\\Database\\\\BaseConnection\\<SQLite3,SQLite3Result\\>\\:\\:\\$escapeChar\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\<string, int\\|string\\>\\) of method CodeIgniter\\\\Database\\\\SQLite3\\\\Connection\\:\\:error\\(\\) should be covariant with return type \\(array\\{code\\: int\\|string\\|null, message\\: string\\|null\\}\\) of method CodeIgniter\\\\Database\\\\BaseConnection\\<SQLite3,SQLite3Result\\>\\:\\:error\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_alterTable\\(\\) has parameter \\$processedFields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has parameter \\$field with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_attributeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_attributeType\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_processColumn\\(\\) has parameter \\$processedField with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_processForeignKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\SQLite3\\\\Connection of property CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:\\$_unsigned type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\PreparedQuery\\:\\:_execute\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\PreparedQuery\\:\\:_prepare\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(SQLite3Result\\|false\\) of method CodeIgniter\\\\Database\\\\SQLite3\\\\PreparedQuery\\:\\:_getResult\\(\\) should be covariant with return type \\(object\\|resource\\|null\\) of method CodeIgniter\\\\Database\\\\BasePreparedQuery\\<SQLite3,SQLite3Stmt,SQLite3Result\\>\\:\\:_getResult\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Result\\:\\:fetchAssoc\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Result\\:\\:getFieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Result\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Result.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Table\\:\\:addForeignKey\\(\\) has parameter \\$foreignKeys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Table\\:\\:addPrimaryKey\\(\\) has parameter \\$fields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Table\\:\\:copyData\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Table\\:\\:dropIndexes\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Table\\:\\:formatFields\\(\\) has parameter \\$fields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Table\\:\\:formatFields\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLite3\\\\Table\\:\\:\\$foreignKeys type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\SQLite3\\\\Table\\:\\:\\$keys type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Utils\\:\\:_backup\\(\\) has parameter \\$prefs with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLite3\\\\Utils\\:\\:\\$optimizeTable is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$optimizeTable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Seeder\\:\\:call\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Seeder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\BaseExceptionHandler\\:\\:collectVars\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/BaseExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\BaseExceptionHandler\\:\\:maskData\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/BaseExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\BaseExceptionHandler\\:\\:maskData\\(\\) has parameter \\$keysToMask with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/BaseExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\BaseExceptionHandler\\:\\:maskData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/BaseExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\BaseExceptionHandler\\:\\:maskSensitiveData\\(\\) has parameter \\$keysToMask with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/BaseExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\BaseExceptionHandler\\:\\:maskSensitiveData\\(\\) has parameter \\$trace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/BaseExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\BaseExceptionHandler\\:\\:maskSensitiveData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/BaseExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionHandler\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/ExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionHandler\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/ExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionHandler\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/ExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionHandler\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/ExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionHandler\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/ExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionHandler\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/ExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:collectVars\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:determineCodes\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:maskData\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:maskData\\(\\) has parameter \\$keysToMask with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:maskData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:maskSensitiveData\\(\\) has parameter \\$keysToMask with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:maskSensitiveData\\(\\) has parameter \\$trace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:maskSensitiveData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:renderBacktrace\\(\\) has parameter \\$backtrace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\Iterator\\:\\:\\$results type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Iterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\Iterator\\:\\:\\$tests type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Iterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Debug/Timer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Timer\\:\\:getTimers\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Timer.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\Timer\\:\\:\\$timers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Timer.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:collectTimelineData\\(\\) has parameter \\$collectors with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:collectTimelineData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:collectVarData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:renderTimeline\\(\\) has parameter \\$collectors with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:renderTimeline\\(\\) has parameter \\$styles with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:renderTimelineRecursive\\(\\) has parameter \\$rows with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:renderTimelineRecursive\\(\\) has parameter \\$styles with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:structureTimelineData\\(\\) has parameter \\$elements with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:structureTimelineData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:display\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/BaseCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:formatTimelineData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/BaseCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:getAsArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/BaseCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:getVarData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/BaseCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:timelineData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/BaseCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Config\\:\\:display\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Database\\:\\:display\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Database\\:\\:formatTimelineData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Database\\:\\:\\$connections type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Database\\:\\:\\$queries type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Events\\:\\:display\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Events\\:\\:formatTimelineData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Files\\:\\:display\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Files.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\History\\:\\:display\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/History.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\History\\:\\:\\$files type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/History.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Logs.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Logs\\:\\:collectLogs\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Logs.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Logs\\:\\:display\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Logs.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Logs\\:\\:\\$data type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Logs.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Log\\\\Logger\\:\\:\\$logCache \\(array\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Logs.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Timers\\:\\:formatTimelineData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Timers.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\View\\\\RendererInterface\\:\\:getData\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Views.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\View\\\\RendererInterface\\:\\:getPerformanceData\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Views.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Views\\:\\:formatTimelineData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Views.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Views\\:\\:getVarData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Views.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Views\\:\\:\\$views type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Views.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SERVER_ADDR\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SERVER_NAME\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 12,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:__construct\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:cleanEmail\\(\\) has parameter \\$email with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:cleanEmail\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:initialize\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:printDebugger\\(\\) has parameter \\$include with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:setArchiveValues\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:setTo\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:stringToArray\\(\\) has parameter \\$email with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:stringToArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:validateEmail\\(\\) has parameter \\$email with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, int given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$BCCArray type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$CCArray type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$archive type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$attachments type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$bitDepths type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$debugMessage type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$headers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$priorities type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$protocols type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$recipients type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Email\\\\Email\\:\\:\\$tmpArchive type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property CodeIgniter\\\\Email\\\\Email\\:\\:\\$func_overload \\(bool\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\EncrypterInterface\\:\\:decrypt\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/EncrypterInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\EncrypterInterface\\:\\:encrypt\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/EncrypterInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Encryption/Encryption.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\Encryption\\:\\:__get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Encryption.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Encryption\\\\Encryption\\:\\:\\$drivers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Encryption.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\Handlers\\\\BaseHandler\\:\\:__get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Encryption/Handlers/OpenSSLHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\Handlers\\\\OpenSSLHandler\\:\\:decrypt\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Handlers/OpenSSLHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\Handlers\\\\OpenSSLHandler\\:\\:encrypt\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Handlers/OpenSSLHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, array\\|string\\|null given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Encryption/Handlers/OpenSSLHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Encryption\\\\Handlers\\\\OpenSSLHandler\\:\\:\\$digestSize type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Handlers/OpenSSLHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Encryption/Handlers/SodiumHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\Handlers\\\\SodiumHandler\\:\\:decrypt\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Handlers/SodiumHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\Handlers\\\\SodiumHandler\\:\\:encrypt\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Handlers/SodiumHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\Handlers\\\\SodiumHandler\\:\\:parseParams\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Handlers/SodiumHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\ArrayCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/ArrayCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\ArrayCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/ArrayCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\ArrayCast\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/ArrayCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\ArrayCast\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/ArrayCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\ArrayCast\\:\\:set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/ArrayCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/BaseCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/BaseCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/BaseCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/BaseCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/BaseCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:set\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/BaseCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\BooleanCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/BooleanCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\BooleanCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/BooleanCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CSVCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CSVCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CSVCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CSVCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CSVCast\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CSVCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CSVCast\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CSVCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CSVCast\\:\\:set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CSVCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CastInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CastInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CastInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CastInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CastInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:set\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/CastInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\DatetimeCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/DatetimeCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\DatetimeCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/DatetimeCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\FloatCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/FloatCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\FloatCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/FloatCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\IntBoolCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\IntBoolCast\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(bool\\|int\\|string\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\IntBoolCast\\:\\:set\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(bool\\|int\\|string\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\IntBoolCast\\:\\:set\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(int\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\IntBoolCast\\:\\:get\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:get\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(int\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\IntBoolCast\\:\\:get\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:get\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\IntegerCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/IntegerCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\IntegerCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/IntegerCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\JsonCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/JsonCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\JsonCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/JsonCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\JsonCast\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/JsonCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\JsonCast\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/JsonCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\JsonCast\\:\\:set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/JsonCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\ObjectCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/ObjectCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\ObjectCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/ObjectCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\StringCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/StringCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\StringCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/StringCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\TimestampCast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/TimestampCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\TimestampCast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/TimestampCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\TimestampCast\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/TimestampCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\URICast\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/URICast.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Cast\\\\URICast\\:\\:get\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Cast/URICast.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:__construct\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:__get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:__set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:castAs\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:fill\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:injectRawData\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:jsonSerialize\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:setAttributes\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:toArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\Entity\\:\\:toRawArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Entity\\\\Entity\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Entity\\\\Entity\\:\\:\\$original type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument \\#1 \\$name \\(\'Config\\\\\\\\Modules\'\\) passed to function config does not extend CodeIgniter\\\\\\\\Config\\\\\\\\BaseConfig\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:listeners\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:on\\(\\) has parameter \\$callback with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:removeListener\\(\\) has parameter \\$listener with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:setFiles\\(\\) has parameter \\$files with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Events\\\\Events\\:\\:\\$listeners type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\PageNotFoundException\\:\\:lang\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/PageNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type int of property CodeIgniter\\\\Exceptions\\\\PageNotFoundException\\:\\:\\$code is not the same as PHPDoc type mixed of overridden property Exception\\:\\:\\$code\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/PageNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Files/File.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Files\\\\File\\:\\:\\$size \\(int\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Files/File.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Files/File.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument \\#1 \\$name \\(\'Config\\\\\\\\Modules\'\\) passed to function config does not extend CodeIgniter\\\\\\\\Config\\\\\\\\BaseConfig\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:checkExcept\\(\\) has parameter \\$paths with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:checkPseudoRegex\\(\\) has parameter \\$paths with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:enableFilters\\(\\) has parameter \\$names with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:getFilters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:getFiltersClass\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:getRequiredFilters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:pathApplies\\(\\) has parameter \\$paths with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:registerArguments\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:runAfter\\(\\) has parameter \\$filterClasses with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:runBefore\\(\\) has parameter \\$filterClasses with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:setToolbarToLast\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array\\<string, array\\<string, array\\<int, string\\>\\>\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Filters\\\\Filters\\:\\:\\$filters type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Filters\\\\Filters\\:\\:\\$filtersClass type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\ForceHTTPS\\:\\:after\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/ForceHTTPS.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\ForceHTTPS\\:\\:before\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/ForceHTTPS.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\InvalidChars\\:\\:checkControl\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/InvalidChars.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\InvalidChars\\:\\:checkControl\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/InvalidChars.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\InvalidChars\\:\\:checkEncoding\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/InvalidChars.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\InvalidChars\\:\\:checkEncoding\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/InvalidChars.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\PageCache\\:\\:after\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/PageCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\PageCache\\:\\:before\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/PageCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\PerformanceMetrics\\:\\:after\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/PerformanceMetrics.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\PerformanceMetrics\\:\\:before\\(\\) has parameter \\$arguments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/PerformanceMetrics.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Format\\\\FormatterInterface\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Format/FormatterInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Format\\\\JSONFormatter\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Format/JSONFormatter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Format\\\\XMLFormatter\\:\\:arrayToXML\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Format/XMLFormatter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Format\\\\XMLFormatter\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Format/XMLFormatter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getArgs\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getCookie\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getCookie\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getGet\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getGet\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getGet\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getGetPost\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getGetPost\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getGetPost\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getOptions\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getPost\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getPost\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getPost\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getPostGet\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getPostGet\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getPostGet\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:getSegments\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:returnNullOrEmptyArray\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:returnNullOrEmptyArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:\\$args type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:\\$options type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:\\$segments type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 10,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:applyBody\\(\\) has parameter \\$curlOptions with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:applyBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:applyMethod\\(\\) has parameter \\$curlOptions with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:applyMethod\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:applyRequestHeaders\\(\\) has parameter \\$curlOptions with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:applyRequestHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:delete\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:get\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:head\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:options\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:parseOptions\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:patch\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:post\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:put\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:request\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:sendRequest\\(\\) has parameter \\$curlOptions with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:setCURLOptions\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:setCURLOptions\\(\\) has parameter \\$curlOptions with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:setCURLOptions\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:setForm\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:setJSON\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:setResponseHeaders\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:\\$defaultConfig type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:\\$defaultOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:\\$redirectDefaults type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addBaseURI\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addChildSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addConnectSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addFontSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addFormAction\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addFrameAncestor\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addFrameSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addImageSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addManifestSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addMediaSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addObjectSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addPluginType\\(\\) has parameter \\$mime with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addSandbox\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addScriptSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addStyleSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addToHeader\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:setDefaultSrc\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$baseURI type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$childSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$connectSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$defaultSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$fontSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$formAction type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$frameAncestors type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$frameSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$imageSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$manifestSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$mediaSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$nonces type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$objectSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$pluginTypes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$reportOnlyHeaders type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$sandbox type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$scriptSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$styleSrc type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$tempHeaders type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:\\$validSources type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_USER_AGENT\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:setCache\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\DownloadResponse\\) of method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:sendBody\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\HTTP\\\\Response\\)\\) of method CodeIgniter\\\\HTTP\\\\Response\\:\\:sendBody\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\DownloadResponse\\) of method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:sendBody\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\)\\) of method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:sendBody\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\) of method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:setContentType\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\HTTP\\\\Response\\)\\) of method CodeIgniter\\\\HTTP\\\\Response\\:\\:setContentType\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\) of method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:setContentType\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\)\\) of method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:setContentType\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\Exceptions\\\\HTTPException\\) of method CodeIgniter\\\\HTTP\\\\Exceptions\\\\HTTPException\\:\\:forInvalidFile\\(\\) should be covariant with return type \\(static\\(CodeIgniter\\\\Exceptions\\\\FrameworkException\\)\\) of method CodeIgniter\\\\Exceptions\\\\FrameworkException\\:\\:forInvalidFile\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Exceptions/HTTPException.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type int of property CodeIgniter\\\\HTTP\\\\Exceptions\\\\RedirectException\\:\\:\\$code is not the same as PHPDoc type mixed of overridden property Exception\\:\\:\\$code\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Exceptions/RedirectException.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Exceptions/RedirectException.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Files\\\\FileCollection\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Files\\\\FileCollection\\:\\:createFileObject\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Files\\\\FileCollection\\:\\:fixFilesArray\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Files\\\\FileCollection\\:\\:fixFilesArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Files\\\\FileCollection\\:\\:getValueDotNotationSyntax\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Files\\\\FileCollection\\:\\:getValueDotNotationSyntax\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array given on the right side\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Files\\\\FileCollection\\:\\:\\$files type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Expression on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/UploadedFile.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\HTTP\\\\Files\\\\UploadedFile\\:\\:\\$originalMimeType is not the same as PHPDoc type string\\|null of overridden property CodeIgniter\\\\Files\\\\File\\:\\:\\$originalMimeType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/UploadedFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Files\\\\UploadedFile\\:\\:\\$error \\(int\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/Files/UploadedFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(bool\\) of method CodeIgniter\\\\HTTP\\\\Files\\\\UploadedFile\\:\\:move\\(\\) should be compatible with return type \\(CodeIgniter\\\\Files\\\\File\\) of method CodeIgniter\\\\Files\\\\File\\:\\:move\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/UploadedFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/UploadedFile.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTPS\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'QUERY_STRING\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'REQUEST_URI\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SCRIPT_NAME\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset array\\|string directly on \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'QUERY_STRING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getCookie\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getCookie\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getCookie\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getFileMultiple\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getFiles\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getGet\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getGet\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getGet\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getGetPost\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getGetPost\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getGetPost\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getJSON\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getJsonVar\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getJsonVar\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getJsonVar\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getOldInput\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getPost\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getPost\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getPost\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getPostGet\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getPostGet\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getPostGet\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getRawInput\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getRawInputVar\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getRawInputVar\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getRawInputVar\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getVar\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getVar\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:getVar\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:negotiate\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:setValidLocales\\(\\) has parameter \\$locales with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\HTTP\\\\URI of property CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:\\$uri is not the same as PHPDoc type CodeIgniter\\\\HTTP\\\\URI\\|null of overridden property CodeIgniter\\\\HTTP\\\\OutgoingRequest\\:\\:\\$uri\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:\\$oldInput type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:\\$validLocales type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'CONTENT_TYPE\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \\(int\\|string\\) directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Message\\:\\:getHeader\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Message\\:\\:setHeader\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Message\\:\\:\\$headerMap type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Message\\:\\:\\$protocolVersion \\(string\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Message\\:\\:\\$validProtocolVersions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\MessageInterface\\:\\:setHeader\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/MessageInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:charset\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:encoding\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:getBestMatch\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:language\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:match\\(\\) has parameter \\$acceptable with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:matchLocales\\(\\) has parameter \\$acceptable with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:matchLocales\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:matchParameters\\(\\) has parameter \\$acceptable with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:matchParameters\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:matchTypes\\(\\) has parameter \\$acceptable with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:matchTypes\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:media\\(\\) has parameter \\$supported with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Negotiate\\:\\:parseHeader\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\OutgoingRequest\\:\\:__construct\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/OutgoingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/OutgoingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\URI\\|null\\) of method CodeIgniter\\\\HTTP\\\\OutgoingRequest\\:\\:getUri\\(\\) should be covariant with return type \\(CodeIgniter\\\\HTTP\\\\URI\\) of method CodeIgniter\\\\HTTP\\\\OutgoingRequestInterface\\:\\:getUri\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/OutgoingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\RedirectResponse\\:\\:route\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/RedirectResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\RedirectResponse\\:\\:with\\(\\) has parameter \\$message with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/RedirectResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, array\\<string, string\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/RedirectResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$_GET on left side of \\?\\? always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/RedirectResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$_POST on left side of \\?\\? always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/RedirectResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Request\\:\\:fetchGlobal\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Request\\:\\:fetchGlobal\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Request\\:\\:fetchGlobal\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Request\\:\\:getEnv\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Request\\:\\:getEnv\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Request\\:\\:getServer\\(\\) has parameter \\$flags with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Request\\:\\:getServer\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Request\\:\\:\\$globals type has no value type specified in iterable type array\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\RequestInterface\\:\\:getServer\\(\\) has parameter \\$index with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/RequestInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'REQUEST_METHOD\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SERVER_PROTOCOL\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SERVER_SOFTWARE\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Response\\:\\:doSetCookie\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Response\\:\\:doSetRawCookie\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Response\\:\\:formatBody\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Response\\:\\:setCache\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Response\\:\\:setCookie\\(\\) has parameter \\$name with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Response\\:\\:setJSON\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Response\\:\\:setXML\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string\\|null given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string\\|null given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, string\\|null given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Response\\:\\:\\$statusCodes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:setCache\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ResponseInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:setCookie\\(\\) has parameter \\$name with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ResponseInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:setJSON\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ResponseInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:setXML\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ResponseInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURI\\:\\:applyParts\\(\\) has parameter \\$parts with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURI\\:\\:baseUrl\\(\\) has parameter \\$relativePath with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURI\\:\\:convertToSegments\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURI\\:\\:parseRelativePath\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURI\\:\\:siteUrl\\(\\) has parameter \\$relativePath with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURI\\:\\:stringifyRelativePath\\(\\) has parameter \\$relativePath with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\SiteURI\\:\\:\\$baseSegments type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\SiteURI\\:\\:\\$segments type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\!\\=\\= between mixed and null will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 10,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URI\\:\\:applyParts\\(\\) has parameter \\$parts with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URI\\:\\:changeSchemeAndPath\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URI\\:\\:getQuery\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URI\\:\\:getSegments\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URI\\:\\:parseStr\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URI\\:\\:setQueryArray\\(\\) has parameter \\$query with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\URI\\:\\:\\$defaultPorts type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\URI\\:\\:\\$fragment \\(string\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\URI\\:\\:\\$host \\(string\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\URI\\:\\:\\$path \\(string\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\URI\\:\\:\\$query type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\URI\\:\\:\\$segments type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\!\\=\\= between mixed and null will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/URI.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_REFERER\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/HTTP/UserAgent.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_USER_AGENT\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/UserAgent.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/UserAgent.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array\\<string, string\\> given on the right side\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/HTTP/UserAgent.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:arrayAttachIndexedValue\\(\\) has parameter \\$indexes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:arrayAttachIndexedValue\\(\\) has parameter \\$result with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:arrayAttachIndexedValue\\(\\) has parameter \\$row with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:arrayAttachIndexedValue\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:arraySearchDot\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:arraySearchDot\\(\\) has parameter \\$indexes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:arraySearchDot\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:dotKeyExists\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:dotSearch\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:dotSearch\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:groupBy\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:groupBy\\(\\) has parameter \\$indexes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:groupBy\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:recursiveCount\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:recursiveDiff\\(\\) has parameter \\$compareWith with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:recursiveDiff\\(\\) has parameter \\$original with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelper\\:\\:recursiveDiff\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/Array/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_deep_search\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_deep_search\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_flatten_with_dots\\(\\) has parameter \\$array with no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_flatten_with_dots\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_group_by\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_group_by\\(\\) has parameter \\$indexes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_group_by\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_sort_by_multiple_keys\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function array_sort_by_multiple_keys\\(\\) has parameter \\$sortColumns with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function dot_array_search\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function dot_array_search\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function get_cookie\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/cookie_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function set_cookie\\(\\) has parameter \\$name with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/cookie_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function directory_map\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function get_dir_file_info\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function get_file_info\\(\\) has parameter \\$returnedValues with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function get_file_info\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function get_filenames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Right side of && is always true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$result might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_button\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_button\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_checkbox\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_checkbox\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_datalist\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_dropdown\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_dropdown\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_dropdown\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_dropdown\\(\\) has parameter \\$selected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_fieldset\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_hidden\\(\\) has parameter \\$name with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_hidden\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_input\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_input\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_label\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_multiselect\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_multiselect\\(\\) has parameter \\$name with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_multiselect\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_multiselect\\(\\) has parameter \\$selected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_open\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_open\\(\\) has parameter \\$hidden with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_open_multipart\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_open_multipart\\(\\) has parameter \\$hidden with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_password\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_password\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_radio\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_radio\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_reset\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_reset\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_submit\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_submit\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_textarea\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_textarea\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_upload\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function form_upload\\(\\) has parameter \\$extra with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function parse_form_attributes\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function parse_form_attributes\\(\\) has parameter \\$default with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, int\\<0, max\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function _list\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function _list\\(\\) has parameter \\$list with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function _media\\(\\) has parameter \\$tracks with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function _media\\(\\) has parameter \\$types with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function audio\\(\\) has parameter \\$src with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function audio\\(\\) has parameter \\$tracks with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function img\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function img\\(\\) has parameter \\$src with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function object\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function ol\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function ol\\(\\) has parameter \\$list with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function script_tag\\(\\) has parameter \\$src with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function ul\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function ul\\(\\) has parameter \\$list with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function video\\(\\) has parameter \\$src with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function video\\(\\) has parameter \\$tracks with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function d\\(\\) has parameter \\$vars with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/kint_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function dd\\(\\) has parameter \\$vars with no value type specified in iterable type array\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/kint_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function format_number\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/number_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/test_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function fake\\(\\) has parameter \\$overrides with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/test_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function fake\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/test_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/text_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function strip_slashes\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/text_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function strip_slashes\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/text_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function word_censor\\(\\) has parameter \\$censored with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/text_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/text_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function anchor\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function anchor\\(\\) has parameter \\$uri with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function anchor_popup\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function base_url\\(\\) has parameter \\$relativePath with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function mailto\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function safe_mailto\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function site_url\\(\\) has parameter \\$relativePath with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Implicit array creation is not allowed \\- variable \\$atts might not exist\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$atts might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Honeypot/Honeypot.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HotReloader\\\\DirectoryHasher\\:\\:hashApp\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HotReloader/DirectoryHasher.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HotReloader\\\\HotReloader\\:\\:sendEvent\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HotReloader/HotReloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in \\|\\|, int given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HotReloader/HotReloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HotReloader\\\\IteratorFilter\\:\\:\\$watchedExtensions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HotReloader/IteratorFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\I18n\\\\Time\\:\\:__get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/Time.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\I18n\\\\Time\\) of method CodeIgniter\\\\I18n\\\\Time\\:\\:setTimestamp\\(\\) should be covariant with return type \\(static\\(DateTimeImmutable\\)\\) of method DateTimeImmutable\\:\\:setTimestamp\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/Time.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\I18n\\\\Time\\) of method CodeIgniter\\\\I18n\\\\Time\\:\\:setTimezone\\(\\) should be covariant with return type \\(static\\(DateTimeImmutable\\)\\) of method DateTimeImmutable\\:\\:setTimezone\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/Time.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/I18n/Time.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\I18n\\\\TimeLegacy\\:\\:__get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/TimeLegacy.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\I18n\\\\TimeLegacy\\) of method CodeIgniter\\\\I18n\\\\TimeLegacy\\:\\:setTimestamp\\(\\) should be covariant with return type \\(static\\(DateTime\\)\\) of method DateTime\\:\\:setTimestamp\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/TimeLegacy.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\I18n\\\\TimeLegacy\\) of method CodeIgniter\\\\I18n\\\\TimeLegacy\\:\\:setTimezone\\(\\) should be covariant with return type \\(static\\(DateTime\\)\\) of method DateTime\\:\\:setTimezone\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/TimeLegacy.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/I18n/TimeLegacy.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:__call\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:__call\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:_text\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:calcAspectRatio\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:calcCropCoords\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:text\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$image \\(CodeIgniter\\\\Images\\\\Image\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$supportTransparency type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$textDefaults type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\) of method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:fit\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Images\\\\ImageHandlerInterface\\)\\) of method CodeIgniter\\\\Images\\\\ImageHandlerInterface\\:\\:fit\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\) of method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:resize\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Images\\\\ImageHandlerInterface\\)\\) of method CodeIgniter\\\\Images\\\\ImageHandlerInterface\\:\\:resize\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Comparison operation "\\>\\=" between \\(array\\|float\\|int\\) and 0 results in an error\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:_text\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:process\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string\\|null of property CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:\\$resource is not the same as PHPDoc type resource\\|null of overridden property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$resource\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$height \\(int\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$width \\(int\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\) of method CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:_resize\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\)\\) of method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:_resize\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(bool\\|CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\) of method CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:_crop\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\)\\) of method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:_crop\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Image.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Image\\:\\:getProperties\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Image.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\ImageHandlerInterface\\:\\:text\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/ImageHandlerInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Language\\\\Language\\:\\:formatMessage\\(\\) has parameter \\$message with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Language\\\\Language\\:\\:formatMessage\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Language\\\\Language\\:\\:getLine\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Language\\\\Language\\:\\:getTranslationOutput\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Language\\\\Language\\:\\:load\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Language\\\\Language\\:\\:parseLine\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Language\\\\Language\\:\\:requireFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Language\\\\Language\\:\\:\\$language type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Language\\\\Language\\:\\:\\$loadedFiles type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Language/Language.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Handlers\\\\BaseHandler\\:\\:__construct\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Log\\\\Handlers\\\\BaseHandler\\:\\:\\$handles type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Handlers\\\\ChromeLoggerHandler\\:\\:__construct\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/ChromeLoggerHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Handlers\\\\ChromeLoggerHandler\\:\\:format\\(\\) has parameter \\$object with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/ChromeLoggerHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Handlers\\\\ChromeLoggerHandler\\:\\:format\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/ChromeLoggerHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Log\\\\Handlers\\\\ChromeLoggerHandler\\:\\:\\$json type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/ChromeLoggerHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Log\\\\Handlers\\\\ChromeLoggerHandler\\:\\:\\$levels type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/ChromeLoggerHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Log/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Handlers\\\\FileHandler\\:\\:__construct\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\=\\=\\= between true and true will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Logger\\:\\:determineFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Logger.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Logger\\:\\:interpolate\\(\\) has parameter \\$context with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Logger.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$level \\(string\\) of method CodeIgniter\\\\Log\\\\Logger\\:\\:log\\(\\) should be contravariant with parameter \\$level \\(mixed\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:log\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Logger.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Log\\\\Logger\\:\\:\\$handlers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Logger.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Log\\\\Logger\\:\\:\\$logCache type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Logger.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Log\\\\Logger\\:\\:\\$loggableLevels type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Logger.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:__call\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:__call\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:__get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:chunk\\(\\) has parameter \\$userFunc with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:doDelete\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:doFind\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:doInsertBatch\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:doProtectFieldsForInsert\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:doUpdate\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:doUpdateBatch\\(\\) has parameter \\$set with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:getIdValue\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:set\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:shouldUpdate\\(\\) has parameter \\$row with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:update\\(\\) has parameter \\$id with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string given on the right side\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string\\|null given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, array\\|int\\|string\\|null given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Model\\:\\:\\$escape type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Model\\:\\:__call\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\BaseModel\\)\\|null\\) of method CodeIgniter\\\\BaseModel\\:\\:__call\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Modules\\\\Modules\\:\\:__set_state\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Modules/Modules.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset mixed directly on \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\Pager\\:\\:getDetails\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\Pager\\:\\:only\\(\\) has parameter \\$queries with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Pager\\\\Pager\\:\\:\\$groups type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Pager\\\\Pager\\:\\:\\$segment type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\PagerInterface\\:\\:getDetails\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/PagerInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\PagerRenderer\\:\\:__construct\\(\\) has parameter \\$details with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/PagerRenderer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Publisher\\\\ContentReplacer\\:\\:replace\\(\\) has parameter \\$replaces with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Publisher/ContentReplacer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Publisher\\\\Publisher\\:\\:replace\\(\\) has parameter \\$replaces with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Publisher/Publisher.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument \\#1 \\$name \\(class\\-string\\) passed to function model does not extend CodeIgniter\\\\\\\\Model\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/BaseResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/RESTful/BaseResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, object\\|string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/BaseResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\ResourceController\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/ResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\ResourceController\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/ResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\ResourceController\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/ResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\ResourceController\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/ResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\ResourceController\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/ResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\ResourceController\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/ResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouter\\:\\:getRoute\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouter\\:\\:scanControllers\\(\\) has parameter \\$segments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouter\\:\\:scanControllers\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @var for variable \\$params has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouterImproved\\:\\:createSegments\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouterImproved.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouterImproved\\:\\:getRoute\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouterImproved.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\AutoRouterImproved\\:\\:\\$moduleRoutes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouterImproved.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouterInterface\\:\\:getRoute\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouterInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type int of property CodeIgniter\\\\Router\\\\Exceptions\\\\RedirectException\\:\\:\\$code is not the same as PHPDoc type mixed of overridden property Exception\\:\\:\\$code\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Exceptions/RedirectException.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:add\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:add\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:addPlaceholder\\(\\) has parameter \\$placeholder with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:buildReverseRoute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:cli\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:cli\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:create\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:create\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:delete\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:delete\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:environment\\(\\) has parameter \\$callback with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:fillRouteParams\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:get\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:get\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:getRoutes\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:group\\(\\) has parameter \\$params with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:group\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:head\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:head\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:map\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:map\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:match\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:match\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:match\\(\\) has parameter \\$verbs with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:options\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:options\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:patch\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:patch\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:post\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:post\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:presenter\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:processArrayCallableSyntax\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:put\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:put\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:resource\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:set404Override\\(\\) has parameter \\$callable with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:view\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array\\<int\\|string, array\\|\\(callable\\)\\> given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\RouteCollection\\:\\:\\$currentOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\RouteCollection\\:\\:\\$routeFiles type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\RouteCollection\\:\\:\\$routes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\RouteCollection\\:\\:\\$routesNames type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\RouteCollection\\:\\:\\$routesOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:add\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:add\\(\\) has parameter \\$to with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:addPlaceholder\\(\\) has parameter \\$placeholder with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:getRoutes\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:set404Override\\(\\) has parameter \\$callable with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'REQUEST_METHOD\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:getDefaultNamespace\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:getFiltersForRoute\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:getRegisteredControllers\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:getRoutesOptions\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:isFiltered\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:setHTTPVerb\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Expression on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:get404Override\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:getMatchedRoute\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:getMatchedRouteOptions\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:params\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:replaceBackReferences\\(\\) has parameter \\$matches with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:scanControllers\\(\\) has parameter \\$segments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:scanControllers\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:setMatchedRoute\\(\\) has parameter \\$handler with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:setRequest\\(\\) has parameter \\$segments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:validateRequest\\(\\) has parameter \\$segments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:validateRequest\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\Router\\:\\:\\$matchedRoute type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\Router\\:\\:\\$matchedRouteOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\Router\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouterInterface\\:\\:params\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouterInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\CheckPhpIni\\:\\:checkIni\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/CheckPhpIni.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\CheckPhpIni\\:\\:outputForCli\\(\\) has parameter \\$output with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/CheckPhpIni.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\CheckPhpIni\\:\\:outputForCli\\(\\) has parameter \\$tbody with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/CheckPhpIni.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\CheckPhpIni\\:\\:outputForCli\\(\\) has parameter \\$thead with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/CheckPhpIni.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\CheckPhpIni\\:\\:outputForWeb\\(\\) has parameter \\$output with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/CheckPhpIni.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\CheckPhpIni\\:\\:outputForWeb\\(\\) has parameter \\$tbody with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/CheckPhpIni.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\CheckPhpIni\\:\\:outputForWeb\\(\\) has parameter \\$thead with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/CheckPhpIni.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Exceptions\\\\SessionException\\:\\:forEmptySavepath\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Exceptions/SessionException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Exceptions\\\\SessionException\\:\\:forInvalidSameSiteSetting\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Exceptions/SessionException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Exceptions\\\\SessionException\\:\\:forInvalidSavePath\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Exceptions/SessionException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Exceptions\\\\SessionException\\:\\:forInvalidSavePathFormat\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Exceptions/SessionException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Exceptions\\\\SessionException\\:\\:forMissingDatabaseTable\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Exceptions/SessionException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Exceptions\\\\SessionException\\:\\:forWriteProtectedSavePath\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Exceptions/SessionException.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Session\\\\Handlers\\\\ArrayHandler\\:\\:\\$cache has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/ArrayHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Session\\\\Handlers\\\\BaseHandler\\:\\:\\$savePath type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\PostgreHandler\\:\\:setSelect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/Database/PostgreHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, mixed given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Session/Handlers/Database/PostgreHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Session/Handlers/DatabaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\DatabaseHandler\\:\\:setSelect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/DatabaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\FileHandler\\:\\:configureSessionIDRegex\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Session\\\\Handlers\\\\FileHandler\\:\\:\\$savePath is not the same as PHPDoc type array\\|string of overridden property CodeIgniter\\\\Session\\\\Handlers\\\\BaseHandler\\:\\:\\$savePath\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$written might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Session/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_X_REQUESTED_WITH\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:__set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:configure\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:configureSidLength\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:destroy\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:getFlashKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:getFlashdata\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:getTempKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:getTempdata\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:initVars\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:keepFlashdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:keepFlashdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:markAsFlashdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:markAsTempdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:push\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:push\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:regenerate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:remove\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:remove\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:removeTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:set\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:set\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:setCookie\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:setFlashdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:setFlashdata\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:setFlashdata\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:setSaveHandler\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:setTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:setTempdata\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:setTempdata\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:startSession\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:stop\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:unmarkFlashdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:unmarkFlashdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:unmarkTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:unmarkTempdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(bool\\) of method CodeIgniter\\\\Session\\\\Session\\:\\:markAsFlashdata\\(\\) should be covariant with return type \\(false\\) of method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:markAsFlashdata\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:destroy\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:getFlashKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:getFlashdata\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:getTempKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:getTempdata\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:keepFlashdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:keepFlashdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:markAsFlashdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:markAsTempdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:regenerate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:remove\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:remove\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:removeTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:set\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:set\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:set\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:setFlashdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:setFlashdata\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:setFlashdata\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:setTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:setTempdata\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:setTempdata\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:unmarkFlashdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:unmarkFlashdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:unmarkTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:unmarkTempdata\\(\\) has parameter \\$key with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Superglobals\\:\\:__construct\\(\\) has parameter \\$get with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Superglobals.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Superglobals\\:\\:__construct\\(\\) has parameter \\$server with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Superglobals.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Superglobals\\:\\:get\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Superglobals.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Superglobals\\:\\:setGetArray\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Superglobals.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Superglobals\\:\\:\\$get type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Superglobals.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Superglobals\\:\\:\\$server type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Superglobals.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:assertCloseEnough\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:getPrivateMethodInvoker\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:mockCache\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:mockEmail\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:mockSession\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:resetFactories\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:resetServices\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:setPrivateProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, CodeIgniter\\\\CodeIgniter given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$headers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$insertCache type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$namespace type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$seed \\(array\\<int, class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\>\\>\\|class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\>\\) does not accept default value of type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$session type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$traits type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Constraints\\\\SeeInDatabase\\:\\:__construct\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Constraints/SeeInDatabase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Constraints\\\\SeeInDatabase\\:\\:\\$data type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Constraints/SeeInDatabase.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-array will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/DOMParser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\DOMParser\\:\\:doXPath\\(\\) has parameter \\$paths with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/DOMParser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:__construct\\(\\) has parameter \\$formatters with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:create\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:createMock\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:getFormatters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:getOverrides\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:make\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:makeArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:resetCounts\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:setFormatters\\(\\) has parameter \\$formatters with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:setOverrides\\(\\) has parameter \\$overrides with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function model expects a valid class string, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Fabricator\\:\\:\\$dateFields type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Fabricator\\:\\:\\$formatters type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Fabricator\\:\\:\\$overrides type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Fabricator\\:\\:\\$tableCounts type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Fabricator\\:\\:\\$tempOverrides type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockBuilder\\:\\:\\$supportedIgnoreStatements type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockCLIConfig\\:\\:\\$CSRFExcludeURIs has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCLIConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCURLRequest\\:\\:getBaseURI\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCURLRequest\\:\\:getDelay\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCURLRequest\\:\\:sendRequest\\(\\) has parameter \\$curlOptions with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCURLRequest\\:\\:setOutput\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCURLRequest\\:\\:setOutput\\(\\) has parameter \\$output with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockCURLRequest\\:\\:\\$curl_options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockCURLRequest\\:\\:\\$output has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCache\\:\\:getMetaData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$insert_id on object\\|resource\\|false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:_close\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:_fieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:shouldReturn\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:shouldReturn\\(\\) has parameter \\$return with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\{code\\: int\\|string\\|null, message\\: string\\|null\\}\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:error\\(\\) should be covariant with return type \\(array\\<string, int\\|string\\>\\) of method CodeIgniter\\\\Database\\\\ConnectionInterface\\<object\\|resource,object\\|resource\\>\\:\\:error\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(bool\\|CodeIgniter\\\\Database\\\\BaseResult\\|CodeIgniter\\\\Database\\\\Query\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:query\\(\\) should be covariant with return type \\(bool\\|CodeIgniter\\\\Database\\\\BaseResult\\<object\\|resource, object\\|resource\\>\\|CodeIgniter\\\\Database\\\\Query\\) of method CodeIgniter\\\\Database\\\\BaseConnection\\<object\\|resource,object\\|resource\\>\\:\\:query\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(bool\\|CodeIgniter\\\\Database\\\\BaseResult\\|CodeIgniter\\\\Database\\\\Query\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:query\\(\\) should be covariant with return type \\(bool\\|CodeIgniter\\\\Database\\\\BaseResult\\<object\\|resource, object\\|resource\\>\\|CodeIgniter\\\\Database\\\\Query\\) of method CodeIgniter\\\\Database\\\\ConnectionInterface\\<object\\|resource,object\\|resource\\>\\:\\:query\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(bool\\|object\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:execute\\(\\) should be covariant with return type \\(object\\|resource\\|false\\) of method CodeIgniter\\\\Database\\\\BaseConnection\\<object\\|resource,object\\|resource\\>\\:\\:execute\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:connect\\(\\) should be covariant with return type \\(object\\|resource\\|false\\) of method CodeIgniter\\\\Database\\\\ConnectionInterface\\<object\\|resource,object\\|resource\\>\\:\\:connect\\(\\)$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:setDatabase\\(\\) should be covariant with return type \\(bool\\) of method CodeIgniter\\\\Database\\\\ConnectionInterface\\<object\\|resource,object\\|resource\\>\\:\\:setDatabase\\(\\)$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockEvents\\:\\:getEventsFile\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockEvents.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockEvents\\:\\:getListeners\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockEvents.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockEvents\\:\\:getSimulate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockEvents.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockEvents\\:\\:unInitialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockEvents.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockFileLogger\\:\\:__construct\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockFileLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockFileLogger\\:\\:\\$destination has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockFileLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockInputOutput\\:\\:getOutputs\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockInputOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockLanguage\\:\\:disableIntlSupport\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockLanguage.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockLanguage\\:\\:requireFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockLanguage.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockLanguage\\:\\:setData\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockLanguage.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockLogger\\:\\:\\$dateFormat has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockLogger\\:\\:\\$handlers has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockLogger\\:\\:\\$threshold has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourceController\\:\\:getFormat\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourceController\\:\\:getModel\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourceController\\:\\:getModelName\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:getFormat\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:getModel\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:getModelName\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResourcePresenter\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResourcePresenter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResponse\\:\\:getPretend\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResponse\\:\\:misbehave\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResult\\:\\:fetchAssoc\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResult\\:\\:getFieldData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockResult\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockResult\\:\\:fetchAssoc\\(\\) should be covariant with return type \\(array\\|false\\|null\\) of method CodeIgniter\\\\Database\\\\BaseResult\\<object\\|resource,object\\|resource\\>\\:\\:fetchAssoc\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockServices\\:\\:\\$classmap has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockServices.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockServices\\:\\:\\$psr4 has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockServices.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockSession\\:\\:regenerate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockSession.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockSession\\:\\:setCookie\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockSession.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockSession\\:\\:setSaveHandler\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockSession.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockSession\\:\\:startSession\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockSession.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockSession\\:\\:\\$didRegenerate has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockSession.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockTable\\:\\:__call\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockTable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockTable\\:\\:__call\\(\\) has parameter \\$method with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockTable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockTable\\:\\:__call\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockTable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\PhpStreamWrapper\\:\\:register\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/PhpStreamWrapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\PhpStreamWrapper\\:\\:restore\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/PhpStreamWrapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\PhpStreamWrapper\\:\\:setContent\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/PhpStreamWrapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\PhpStreamWrapper\\:\\:stream_stat\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/PhpStreamWrapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertJSONExact\\(\\) has parameter \\$test with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertJSONFragment\\(\\) has parameter \\$fragment with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Throttle\\\\Throttler\\:\\:\\$testTime \\(int\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Throttle/Throttler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Typography\\\\Typography\\:\\:protectCharacters\\(\\) has parameter \\$match with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Typography/Typography.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Typography\\\\Typography\\:\\:\\$innerBlockRequired type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Typography/Typography.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\CreditCardRules\\:\\:\\$cards type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/CreditCardRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\DotArrayFilter\\:\\:filter\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/DotArrayFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\DotArrayFilter\\:\\:filter\\(\\) has parameter \\$indexes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/DotArrayFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\DotArrayFilter\\:\\:filter\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/DotArrayFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\DotArrayFilter\\:\\:run\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/DotArrayFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\DotArrayFilter\\:\\:run\\(\\) has parameter \\$indexes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/DotArrayFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\DotArrayFilter\\:\\:run\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/DotArrayFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array\\|null given\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/Validation/FileRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:differs\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:field_exists\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:field_exists\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:is_not_unique\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:is_unique\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:matches\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:required\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:required_with\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Rules\\:\\:required_without\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\CreditCardRules\\:\\:valid_cc_number\\(\\) has parameter \\$ccNumber with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/CreditCardRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:alpha\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:alpha_dash\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:alpha_numeric\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:alpha_numeric_punct\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:alpha_numeric_space\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:alpha_space\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:decimal\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:hex\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:integer\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:is_natural\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:is_natural_no_zero\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:numeric\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:regex_match\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:string\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:timezone\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:valid_base64\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:valid_date\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:valid_email\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:valid_emails\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:valid_ip\\(\\) has parameter \\$ip with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:valid_json\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:valid_url\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRules\\:\\:valid_url_strict\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:differs\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:differs\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:equals\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:exact_length\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:field_exists\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:field_exists\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:greater_than\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:greater_than_equal_to\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:in_list\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:is_not_unique\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:is_not_unique\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:is_unique\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:is_unique\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:less_than\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:less_than_equal_to\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:matches\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:matches\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:max_length\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:min_length\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:not_equals\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:not_in_list\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:required\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:required_with\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:required_with\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:required_without\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\Rules\\:\\:required_without\\(\\) has parameter \\$str with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:check\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:check\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:fillPlaceholders\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:fillPlaceholders\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:fillPlaceholders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:getRules\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:getValidated\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:isClosure\\(\\) has parameter \\$rule with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:isStringList\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:loadRuleGroup\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processIfExist\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processIfExist\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processIfExist\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processPermitEmpty\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processPermitEmpty\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processPermitEmpty\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processPermitEmpty\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processRules\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processRules\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:processRules\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:retrievePlaceholders\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:retrievePlaceholders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:run\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:run\\(\\) has parameter \\$dbGroup with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:setRule\\(\\) has parameter \\$errors with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:setRule\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:setRules\\(\\) has parameter \\$errors with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:setRules\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:splitRules\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\Validation\\:\\:\\$customErrors type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\Validation\\:\\:\\$data type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\Validation\\:\\:\\$errors type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\Validation\\:\\:\\$ruleSetFiles type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\Validation\\:\\:\\$ruleSetInstances type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\Validation\\:\\:\\$rules type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\Validation\\:\\:\\$validated type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:check\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:check\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:getRules\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:getValidated\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:loadRuleGroup\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:run\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:run\\(\\) has parameter \\$dbGroup with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:setRule\\(\\) has parameter \\$errors with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:setRule\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:setRules\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:setRules\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method CodeIgniter\\\\Config\\\\Factories\\:\\:cells\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:determineClass\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:getMethodParams\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:getMethodParams\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:prepareParams\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:prepareParams\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:render\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:renderCell\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:renderSimpleClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cells\\\\Cell\\:\\:fill\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cells\\\\Cell\\:\\:getNonPublicProperties\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cells\\\\Cell\\:\\:getPublicProperties\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cells\\\\Cell\\:\\:includeComputedProperties\\(\\) has parameter \\$properties with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cells\\\\Cell\\:\\:includeComputedProperties\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cells\\\\Cell\\:\\:view\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/system/Traits/PropertiesTrait\\.php\\:49\\:\\:getProperties\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Filters\\:\\:default\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:addPlugin\\(\\) has parameter \\$callback with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:applyFilters\\(\\) has parameter \\$filters with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:objectToArray\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:objectToArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:parse\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:parse\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:parsePair\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:parsePair\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:parseSingle\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:prepareReplacement\\(\\) has parameter \\$matches with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:render\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:renderString\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:replaceSingle\\(\\) has parameter \\$pattern with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:setData\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Parser\\:\\:\\$dataContexts type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Parser\\:\\:\\$noparseBlocks type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\RendererInterface\\:\\:render\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/RendererInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\RendererInterface\\:\\:renderString\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/RendererInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\RendererInterface\\:\\:setData\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/RendererInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:__construct\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:_defaultTemplate\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:_prepArgs\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:_prepArgs\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:_setFromArray\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:generate\\(\\) has parameter \\$tableData with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:makeColumns\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:makeColumns\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:setTemplate\\(\\) has parameter \\$template with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Table\\:\\:\\$footing type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Table\\:\\:\\$function type has no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Table\\:\\:\\$heading type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Table\\:\\:\\$rows type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Table\\:\\:\\$template type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:getData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:getPerformanceData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:include\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:render\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:renderString\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:setData\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\View\\:\\:\\$data type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\View\\:\\:\\$performanceData type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\View\\:\\:\\$renderVars type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\View\\:\\:\\$sections type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\View\\:\\:\\$tempData type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Cells\\\\StarterCell\\:\\:hello\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Cells/StarterCell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Cells\\\\StarterCell\\:\\:hello\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Cells/StarterCell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\LanguageCommand\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\LanguageCommand\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\LanguageCommand\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\LanguageCommand\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\LanguageCommand\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\LanguageCommand\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\LanguageCommand\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Commands\\\\LanguageCommand\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/LanguageCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Commands\\\\ParamsReveal\\:\\:\\$args has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/ParamsReveal.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\Unsuffixable\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/Unsuffixable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\Unsuffixable\\:\\:generateClass\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/Unsuffixable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\Unsuffixable\\:\\:generateView\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/Unsuffixable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\Unsuffixable\\:\\:parseTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/Unsuffixable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\Unsuffixable\\:\\:parseTemplate\\(\\) has parameter \\$replace with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/Unsuffixable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\Unsuffixable\\:\\:parseTemplate\\(\\) has parameter \\$search with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/Unsuffixable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Commands\\\\Unsuffixable\\:\\:renderTemplate\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/Unsuffixable.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Commands\\\\Unsuffixable\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Commands/Unsuffixable.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Config\\\\BadRegistrar\\:\\:RegistrarConfig\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Config/BadRegistrar.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$filters might not be defined\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/_support/Config/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$routes might not be defined\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/_support/Config/Routes.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Config\\\\TestRegistrar\\:\\:RegistrarConfig\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Config/TestRegistrar.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Config\\\\Validation\\:\\:\\$signup has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Config/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Config\\\\Validation\\:\\:\\$signup_errors has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Config/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Hello\\:\\:index\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Hello.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Newautorouting\\:\\:getIndex\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Newautorouting.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Newautorouting\\:\\:postSave\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Newautorouting.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Newautorouting\\:\\:postSave\\(\\) has parameter \\$c with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Newautorouting.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:echoJson\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:goaway\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:index\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:index3\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:json\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:oops\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:pop\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:toindex\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:weasel\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Popcorn\\:\\:xml\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Popcorn.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Remap\\:\\:_remap\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Remap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Remap\\:\\:_remap\\(\\) has parameter \\$method with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Remap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Remap\\:\\:_remap\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Remap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Remap\\:\\:abc\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Remap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Controllers\\\\Remap\\:\\:index\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Controllers/Remap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBase64\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBase64.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBase64\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBase64.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(string\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBase64\\:\\:get\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:get\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBase64.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(string\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBase64\\:\\:get\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:get\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBase64.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(string\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBase64\\:\\:set\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBase64.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(string\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBase64\\:\\:set\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBase64.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBinaryUUID\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBinaryUUID.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBinaryUUID\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBinaryUUID.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$binary \\(string\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBinaryUUID\\:\\:get\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:get\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBinaryUUID.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$binary \\(string\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBinaryUUID\\:\\:get\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:get\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBinaryUUID.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$string \\(string\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBinaryUUID\\:\\:set\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBinaryUUID.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$string \\(string\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastBinaryUUID\\:\\:set\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastBinaryUUID.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastPassParameters\\:\\:set\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastPassParameters.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastPassParameters\\:\\:set\\(\\) should be covariant with return type \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastPassParameters.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method Tests\\\\Support\\\\Entity\\\\Cast\\\\CastPassParameters\\:\\:set\\(\\) should be covariant with return type \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/Cast/CastPassParameters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Entity\\\\CustomUser\\:\\:__construct\\(\\) has parameter \\$email with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/CustomUser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Entity\\\\CustomUser\\:\\:__get\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/CustomUser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Entity\\\\CustomUser\\:\\:reconstruct\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/CustomUser.php',
];
$ignoreErrors[] = [
	'message' => '#^Unsafe usage of new static\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/CustomUser.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Entity\\\\User\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Entity/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Language\\\\SecondMockLanguage\\:\\:loaded\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Language/SecondMockLanguage.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Language\\\\SecondMockLanguage\\:\\:loadem\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Language/SecondMockLanguage.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Log\\\\Handlers\\\\TestHandler\\:\\:__construct\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Log/Handlers/TestHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Log\\\\Handlers\\\\TestHandler\\:\\:getLogs\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Log/Handlers/TestHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Log\\\\Handlers\\\\TestHandler\\:\\:\\$logs type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Log/Handlers/TestHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:tableExists\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/MigrationTestMigrations/Database/Migrations/2018-01-24-102302_Another_migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterDeleteMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterDeleteMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterFindMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterFindMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterInsertBatchMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterInsertBatchMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterInsertMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterInsertMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterUpdateBatchMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterUpdateBatchMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterUpdateMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:afterUpdateMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeDeleteMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeDeleteMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeFindMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeFindMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeInsertBatchMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeInsertBatchMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeInsertMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeInsertMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeUpdateBatchMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeUpdateBatchMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeUpdateMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:beforeUpdateMethod\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Models\\\\EventModel\\:\\:hasToken\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Models\\\\EventModel\\:\\:\\$beforeFindReturnData has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Models\\\\EventModel\\:\\:\\$eventData has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Models\\\\EventModel\\:\\:\\$tokens has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/EventModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Models\\\\JobModel\\:\\:\\$description has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/JobModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Models\\\\JobModel\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/JobModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Models\\\\UserModel\\:\\:\\$country has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Models\\\\UserModel\\:\\:\\$email has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\Models\\\\UserModel\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\SomeEntity\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/SomeEntity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:getPrivate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:getStaticPrivate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:privateMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:privateMethod\\(\\) has parameter \\$param1 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:privateMethod\\(\\) has parameter \\$param2 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:privateMethod\\(\\) is unused\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:privateStaticMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:privateStaticMethod\\(\\) has parameter \\$param1 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:privateStaticMethod\\(\\) has parameter \\$param2 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Static method Tests\\\\Support\\\\Test\\\\TestForReflectionHelper\\:\\:privateStaticMethod\\(\\) is unused\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Test/TestForReflectionHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Validation\\\\TestRules\\:\\:array_count\\(\\) has parameter \\$count with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Validation/TestRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Validation\\\\TestRules\\:\\:array_count\\(\\) has parameter \\$value with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Validation/TestRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Validation\\\\TestRules\\:\\:check_object_rule\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Validation/TestRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Validation\\\\TestRules\\:\\:check_object_rule\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Validation/TestRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\Validation\\\\TestRules\\:\\:customError\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/Validation/TestRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\Cells\\\\ListerCell\\:\\:getItemsProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/ListerCell.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tests\\\\Support\\\\View\\\\Cells\\\\ListerCell\\:\\:\\$items type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/ListerCell.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$value might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/addition.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$message might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/awesome_cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$this might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/colors.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$greeting might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/greeting.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$name might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/greeting.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$items might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/lister.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$value might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/multiplier.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$message might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Cells/notice.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\OtherCells\\\\SampleClass\\:\\:hello\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/OtherCells/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:echobox\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:echobox\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:hello\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:index\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:staticEcho\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:staticEcho\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:work\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:work\\(\\) has parameter \\$p1 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:work\\(\\) has parameter \\$p2 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClass\\:\\:work\\(\\) has parameter \\$p4 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Support\\\\View\\\\SampleClassWithInitController\\:\\:index\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/SampleClassWithInitController.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$testString might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/View/Views/simple.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\Mycontroller\\:\\:getSomemethod\\(\\) has parameter \\$first with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/_controller/Mycontroller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\foo\\\\bar\\\\baz\\\\Some_controller\\:\\:some_method\\(\\) has parameter \\$first with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/_support/_controller/foo/bar/baz/Some_controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning mixed directly on offset \'CONTENT_TYPE\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\API\\\\ResponseTraitTest\\:\\:createRequestAndResponse\\(\\) has parameter \\$userHeaders with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\API\\\\ResponseTraitTest\\:\\:invoke\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\API\\\\ResponseTraitTest\\:\\:invoke\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\API\\\\ResponseTraitTest\\:\\:makeController\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\API\\\\ResponseTraitTest\\:\\:makeController\\(\\) has parameter \\$userHeaders with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\API\\\\ResponseTraitTest\\:\\:tryValidContentType\\(\\) has parameter \\$contentType with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\API\\\\ResponseTraitTest\\:\\:tryValidContentType\\(\\) has parameter \\$mimeType with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:__construct\\(\\) has parameter \\$formatter with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:__construct\\(\\) has parameter \\$request with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:__construct\\(\\) has parameter \\$response with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:__construct\\(\\) has parameter \\$formatter with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:__construct\\(\\) has parameter \\$request with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:__construct\\(\\) has parameter \\$response with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:__construct\\(\\) has parameter \\$formatter with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:__construct\\(\\) has parameter \\$request with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:__construct\\(\\) has parameter \\$response with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:__construct\\(\\) has parameter \\$request with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:__construct\\(\\) has parameter \\$response with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:fail\\(\\) has parameter \\$messages with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:format\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:respond\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:respondCreated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:respondDeleted\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:respondUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:\\$formatter has no type specified\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:\\$request has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:116\\:\\:\\$response has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:\\$formatter has no type specified\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:\\$request has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:177\\:\\:\\$response has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:\\$formatter has no type specified\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:\\$request has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:299\\:\\:\\$response has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:\\$format \\(\'html\'\\|\'json\'\\|\'xml\'\\|null\\) does not accept \'txt\'\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:\\$request has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/API/ResponseTraitTest\\.php\\:639\\:\\:\\$response has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/API/ResponseTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\AutoReview\\\\ComposerJsonTest\\:\\:checkConfig\\(\\) has parameter \\$fromComponent with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/ComposerJsonTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\AutoReview\\\\ComposerJsonTest\\:\\:checkConfig\\(\\) has parameter \\$fromMain with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/ComposerJsonTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\AutoReview\\\\ComposerJsonTest\\:\\:getComposerJson\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/ComposerJsonTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\AutoReview\\\\ComposerJsonTest\\:\\:\\$devComposer type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/ComposerJsonTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\AutoReview\\\\ComposerJsonTest\\:\\:\\$frameworkComposer type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/ComposerJsonTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\AutoReview\\\\ComposerJsonTest\\:\\:\\$starterComposer type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/ComposerJsonTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\AutoReview\\\\FrameworkCodeTest\\:\\:getTestClasses\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/FrameworkCodeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\AutoReview\\\\FrameworkCodeTest\\:\\:provideEachTestClassHasCorrectGroupAnnotation\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/FrameworkCodeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\AutoReview\\\\FrameworkCodeTest\\:\\:\\$recognizedGroupAnnotations type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/FrameworkCodeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\AutoReview\\\\FrameworkCodeTest\\:\\:\\$testClasses type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/AutoReview/FrameworkCodeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Autoloader\\\\AutoloaderTest\\:\\:getPrivateMethodInvoker\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Autoloader/AutoloaderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Autoloader\\\\AutoloaderTest\\:\\:setPrivateProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Autoloader/AutoloaderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Autoloader/AutoloaderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Autoloader\\\\FileLocatorInterface\\:\\:__destruct\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Autoloader/FileLocatorCachedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Autoloader\\\\FileLocatorInterface\\:\\:deleteCache\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Autoloader/FileLocatorCachedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 3 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'ignored\', \'b\', \'c\', \'\\-\\-parm\', \'pvalue\', \'d\', \'\\-\\-p2\', \'\\-\\-p3\', \'value 3\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'ignored\', \'b\', \'c\', \'\\-\\-parm\', \'pvalue\', \'d\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'ignored\', \'b\', \'c\', \'d\', \'\\-\\-parm\', \'pvalue\', \'d2\', \'da\\-sh\', \'\\-\\-fix\', \'\\-\\-opt\\-in\', \'sure\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'ignored\', \'b\', \'c\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLITest\\:\\:provideTable\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLITest\\:\\:testTable\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLITest\\:\\:testTable\\(\\) has parameter \\$tbody with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLITest\\:\\:testTable\\(\\) has parameter \\$thead with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/CLITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'app\\.baseURL\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/ConsoleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'argv\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/ConsoleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'http\\://example\\.com/\' directly on offset \'app\\.baseURL\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/ConsoleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning int\\<1, max\\> directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/ConsoleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning non\\-empty\\-array\\<int\\|string, \'spark\'\\|array\\> directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/ConsoleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\ConsoleTest\\:\\:initCLI\\(\\) has parameter \\$command with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CLI/ConsoleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @var has invalid value \\(@var FileVarExportHandler\\|CacheInterface\\)\\: Unexpected token "@var", expected type at offset 16$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/FactoriesCacheFileHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Config\\\\BaseConfig\\:\\:\\$baseURL\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/FactoriesCacheFileVarExportHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\BaseHandlerTest\\:\\:provideValidateKeyInvalidType\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/BaseHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Calling new DummyHandler\\(\\) directly is incomplete to get the cache instance\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/DummyHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Calling new BaseTestFileHandler\\(\\) directly is incomplete to get the cache instance\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/FileHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Calling new FileHandler\\(\\) directly is incomplete to get the cache instance\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/FileHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\BaseTestFileHandler\\:\\:getFileInfoTest\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/FileHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandlerTest\\:\\:getKeyArray\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/FileHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandlerTest\\:\\:provideSaveMode\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/FileHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Calling new MemcachedHandler\\(\\) directly is incomplete to get the cache instance\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/MemcachedHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\MemcachedHandlerTest\\:\\:getKeyArray\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/MemcachedHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Calling new PredisHandler\\(\\) directly is incomplete to get the cache instance\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/PredisHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\PredisHandlerTest\\:\\:getKeyArray\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/PredisHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Calling new RedisHandler\\(\\) directly is incomplete to get the cache instance\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/RedisHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\RedisHandlerTest\\:\\:getKeyArray\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/Handlers/RedisHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/ResponseCacheTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'public/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/ResponseCacheTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning non\\-empty\\-array\\<int, string\\> directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/ResponseCacheTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning non\\-falsy\\-string directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/ResponseCacheTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\ResponseCacheTest\\:\\:createIncomingRequest\\(\\) has parameter \\$query with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cache/ResponseCacheTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Cache/ResponseCacheTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/cannotFound\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/cli\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/example\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/image\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 20,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/pages/about\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/test\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'CLI\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'HTTP/1\\.1\' directly on offset \'SERVER_PROTOCOL\' of \\$_SERVER is discouraged\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'HTTP/2\\.0\' directly on offset \'SERVER_PROTOCOL\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'HTTP/3\\.0\' directly on offset \'SERVER_PROTOCOL\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'public/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 1 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 2 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 27,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'/\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 12,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'cli\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'example\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'image\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'pages/about\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 7,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning non\\-falsy\\-string directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:pretend\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniterTest\\:\\:providePageCacheWithCacheQueryString\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniterTest\\:\\:testPageCacheWithCacheQueryString\\(\\) has parameter \\$cacheQueryStringValue with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniterTest\\:\\:testPageCacheWithCacheQueryString\\(\\) has parameter \\$testingUrls with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$to of method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:add\\(\\) expects array\\|\\(Closure\\(mixed \\.\\.\\.\\)\\: \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|void\\)\\)\\|string, Closure\\(mixed\\)\\: \\(CodeIgniter\\\\HTTP\\\\DownloadResponse\\|null\\) given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$to of method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:add\\(\\) expects array\\|\\(Closure\\(mixed \\.\\.\\.\\)\\: \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|void\\)\\)\\|string, Closure\\(mixed\\)\\: CodeIgniter\\\\HTTP\\\\ResponseInterface given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$to of method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:add\\(\\) expects array\\|\\(Closure\\(mixed \\.\\.\\.\\)\\: \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|void\\)\\)\\|string, Closure\\(mixed\\)\\: non\\-falsy\\-string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$to of method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:add\\(\\) expects array\\|\\(Closure\\(mixed \\.\\.\\.\\)\\: \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|void\\)\\)\\|string, Closure\\(mixed\\)\\: void given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CodeIgniterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Tests\\\\Support\\\\Commands\\\\AppInfo\\:\\:\\$foobar\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/BaseCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/CellGeneratorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/CommandGeneratorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\CommandTest\\:\\:getBuffer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/CommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\CommandTest\\:\\:provideCommandParsesArgsCorrectly\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/CommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\CommandTest\\:\\:testCommandParsesArgsCorrectly\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/CommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/ControllerGeneratorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\CreateDatabaseTest\\:\\:getBuffer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/CreateDatabaseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'production\' directly on offset \'CI_ENVIRONMENT\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/EnvironmentCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'CI_ENVIRONMENT\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/EnvironmentCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\FilterCheckTest\\:\\:getBuffer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/FilterCheckTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'encryption\\.key\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/GenerateKeyTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\HelpCommandTest\\:\\:getBuffer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/HelpCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\InfoCacheTest\\:\\:getBuffer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/InfoCacheTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/ModelGeneratorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\RoutesTest\\:\\:getBuffer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/RoutesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$mock of static method CodeIgniter\\\\Config\\\\BaseService\\:\\:injectMock\\(\\) expects object, null given\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Commands/RoutesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/ScaffoldGeneratorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinderTest\\:\\:getActualTranslationFourKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Translation/LocalizationFinderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinderTest\\:\\:getActualTranslationOneKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Translation/LocalizationFinderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Translation\\\\LocalizationFinderTest\\:\\:getActualTranslationThreeKeys\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Translation/LocalizationFinderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\ConfigCheckTest\\:\\:getBuffer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/ConfigCheckTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\NamespacesTest\\:\\:getBuffer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/NamespacesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\AutoRouteCollectorTest\\:\\:createAutoRouteCollector\\(\\) has parameter \\$filterConfigFilters with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/AutoRouterImproved/AutoRouteCollectorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\Controllers\\\\Dash_folder\\\\Dash_controller\\:\\:getDash_method\\(\\) has parameter \\$p1 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/AutoRouterImproved/Controllers/Dash_folder/Dash_controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\Controllers\\\\Dash_folder\\\\Dash_controller\\:\\:getDash_method\\(\\) has parameter \\$p2 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/AutoRouterImproved/Controllers/Dash_folder/Dash_controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\Controllers\\\\Dash_folder\\\\Dash_controller\\:\\:getSomemethod\\(\\) has parameter \\$p1 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/AutoRouterImproved/Controllers/Dash_folder/Dash_controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\AutoRouterImproved\\\\Controllers\\\\SubDir\\\\BlogController\\:\\:getSomeMethod\\(\\) has parameter \\$first with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/AutoRouterImproved/Controllers/SubDir/BlogController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\FilterFinderTest\\:\\:createFilters\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/FilterFinderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\FilterFinderTest\\:\\:createRouteCollection\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/FilterFinderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\FilterFinderTest\\:\\:createRouteCollection\\(\\) should return CodeIgniter\\\\Router\\\\RouteCollection but returns CodeIgniter\\\\Router\\\\RouteCollectionInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/FilterFinderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\FilterFinderTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\) does not accept CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/FilterFinderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Utilities\\\\Routes\\\\SampleURIGeneratorTest\\:\\:provideGet\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Commands/Utilities/Routes/SampleURIGeneratorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'foo\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsSendTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'foo\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument \\#1 \\$name \\(\'CodeIgniter\\\\\\\\UnexsistenceClass\'\\) passed to function model does not extend CodeIgniter\\\\\\\\Model\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'bar\' directly on offset \'foo\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Class CodeIgniter\\\\UnexsistenceClass not found\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CommonFunctionsTest\\:\\:provideCleanPathActuallyCleaningThePaths\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function model expects a valid class string, \'JobModel\' given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$context of function esc expects \'attr\'\\|\'css\'\\|\'html\'\\|\'js\'\\|\'raw\'\\|\'url\', \'0\' given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$context of function esc expects \'attr\'\\|\'css\'\\|\'html\'\\|\'js\'\\|\'raw\'\\|\'url\', \'bogus\' given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/CommonFunctionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Function foo_bar_baz not found\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Inner named functions are not supported by PHPStan\\. Consider refactoring to an anonymous function, class method, or a top\\-level\\-defined function\\. See issue \\#165 \\(https\\://github\\.com/phpstan/phpstan/issues/165\\) for more details\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\CommonHelperTest\\:\\:\\$dummyHelpers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to unknown service method \'bar\'\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonSingleServiceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to unknown service method \'baz\'\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonSingleServiceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to unknown service method \'caches\'\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonSingleServiceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to unknown service method \'foo\'\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonSingleServiceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to unknown service method \'timers\'\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonSingleServiceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to unknown service method string\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/CommonSingleServiceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CommonSingleServiceTest\\:\\:provideServiceNames\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/CommonSingleServiceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'BAR\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/DotEnvTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'FOO\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/DotEnvTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'NULL\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/DotEnvTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SPACED\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/DotEnvTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SimpleConfig_simple_name\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/DotEnvTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'TT\' directly on offset \'SER_VAR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/DotEnvTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\DotEnvTest\\:\\:provideLoadsVars\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/DotEnvTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$file of class CodeIgniter\\\\Config\\\\DotEnv constructor expects string, int given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/DotEnvTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method CodeIgniter\\\\Config\\\\Factories\\:\\:cells\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method CodeIgniter\\\\Config\\\\Factories\\:\\:tedwigs\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method CodeIgniter\\\\Config\\\\Factories\\:\\:widgets\\(\\)\\.$#',
	'count' => 13,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\FactoriesTest\\:\\:getFactoriesStaticProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\FactoriesTest\\:\\:getFactoriesStaticProperty\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\FactoriesTest\\:\\:testGetComponentInstances\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\FactoriesTest\\:\\:testIsUpdated\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\FactoriesTest\\:\\:testSetComponentInstances\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\FactoriesTest\\:\\:testSetComponentInstances\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$expected of method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) expects class\\-string\\<Config\\\\TestRegistrar\\>, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function model expects a valid class string, \'CodeIgniter\\\\\\\\Shield\\\\\\\\Models\\\\\\\\UserModel\' given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$classname of static method CodeIgniter\\\\Config\\\\Factories\\:\\:define\\(\\) expects class\\-string, string given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Config/FactoriesTest\\.php\\:88\\:\\:\\$widgets has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/FactoriesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\MimesTest\\:\\:provideGuessExtensionFromType\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/MimesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\MimesTest\\:\\:provideGuessTypeFromExtension\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/MimesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method Tests\\\\Support\\\\Config\\\\Services\\:\\:SeSsIoN\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/ServicesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method Tests\\\\Support\\\\Config\\\\Services\\:\\:SeSsIoNs\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/ServicesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method Tests\\\\Support\\\\Config\\\\Services\\:\\:redirectResponse\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/ServicesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\ServicesTest\\:\\:\\$original type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/ServicesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property RegistrarConfig\\:\\:\\$bar has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/RegistrarConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property RegistrarConfig\\:\\:\\$foo has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/RegistrarConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$FOO has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$QEMPTYSTR has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$QFALSE has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$QZERO has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$QZEROSTR has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$alpha has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$bravo has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$charlie has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$crew has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$default has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$delta has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$dessert has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$echo has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$first has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$float has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$foxtrot has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$fruit has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$golf has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$int has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$longie has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$one_deep has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$onedeep has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$onedeep_value has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$password has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$second has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$shortie has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Property SimpleConfig\\:\\:\\$simple has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Config/fixtures/SimpleConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Dead catch \\- CodeIgniter\\\\HTTP\\\\Exceptions\\\\RedirectException is never thrown in the try block\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/ControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/ControllerTest\\.php\\:128\\:\\:\\$signup has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/ControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/ControllerTest\\.php\\:128\\:\\:\\$signup_errors has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/ControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/ControllerTest\\.php\\:151\\:\\:\\$signup has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/ControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$cookies of class CodeIgniter\\\\Cookie\\\\CookieStore constructor expects array\\<CodeIgniter\\\\Cookie\\\\Cookie\\>, array\\<int, DateTimeImmutable\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieStoreTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Cookie\\\\CookieStoreTest\\:\\:\\$defaults type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieStoreTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Expression "\\$cookie\\[\'expiry\'\\]" on a separate line does not do anything\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cookie\\\\CookieTest\\:\\:provideConfigPrefix\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cookie\\\\CookieTest\\:\\:provideInvalidExpires\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cookie\\\\CookieTest\\:\\:provideSetCookieHeaderCreation\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cookie\\\\CookieTest\\:\\:testSetCookieHeaderCreation\\(\\) has parameter \\$changed with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$options of class CodeIgniter\\\\Cookie\\\\Cookie constructor expects array\\<string, bool\\|int\\|string\\>, array\\<string, DateTimeImmutable\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Cookie\\\\CookieTest\\:\\:\\$defaults type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Cookie/CookieTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Implicit array creation is not allowed \\- variable \\$array does not exist\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:createDataConverter\\(\\) has parameter \\$extractor with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:createDataConverter\\(\\) has parameter \\$handlers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:createDataConverter\\(\\) has parameter \\$reconstructor with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:createDataConverter\\(\\) has parameter \\$types with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:provideConvertDataFromDB\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:provideConvertDataToDB\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:testConvertDataFromDB\\(\\) has parameter \\$dbData with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:testConvertDataFromDB\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:testConvertDataFromDB\\(\\) has parameter \\$types with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:testConvertDataToDB\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:testConvertDataToDB\\(\\) has parameter \\$phpData with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\DataConverter\\\\DataConverterTest\\:\\:testConvertDataToDB\\(\\) has parameter \\$types with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DataConverter/DataConverterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:\\$foobar\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/BaseConnectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnectionTest\\:\\:provideProtectIdentifiers\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/BaseConnectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnectionTest\\:\\:\\$failoverOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/BaseConnectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseConnectionTest\\:\\:\\$options type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/BaseConnectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseQueryTest\\:\\:provideHighlightQueryKeywords\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/BaseQueryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseQueryTest\\:\\:provideIsWriteType\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/BaseQueryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$from of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:from\\(\\) expects array\\|string, null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Builder/FromTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$subject of function str_replace expects array\\|string, CodeIgniter\\\\Database\\\\ResultInterface given\\.$#',
	'count' => 10,
	'path' => __DIR__ . '/tests/system/Database/Builder/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$subject of function str_replace expects array\\|string, CodeIgniter\\\\Database\\\\ResultInterface\\|false given\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/tests/system/Database/Builder/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Test\\\\Mock\\\\MockConnection of property CodeIgniter\\\\Database\\\\Builder\\\\InsertTest\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Builder/InsertTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Test\\\\Mock\\\\MockConnection of property CodeIgniter\\\\Database\\\\Builder\\\\UnionTest\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Builder/UnionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Test\\\\Mock\\\\MockConnection of property CodeIgniter\\\\Database\\\\Builder\\\\UpdateTest\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Builder/UpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Test\\\\Mock\\\\MockConnection of property CodeIgniter\\\\Database\\\\Builder\\\\WhenTest\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Builder/WhenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Builder\\\\WhereTest\\:\\:provideWhereInEmptyValuesThrowInvalidArgumentException\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Builder/WhereTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Builder\\\\WhereTest\\:\\:provideWhereInvalidKeyThrowInvalidArgumentException\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Builder/WhereTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Test\\\\Mock\\\\MockConnection of property CodeIgniter\\\\Database\\\\Builder\\\\WhereTest\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Builder/WhereTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConfigTest\\:\\:getPrivateMethodInvoker\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/ConfigTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConfigTest\\:\\:provideConvertDSN\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/ConfigTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ConfigTest\\:\\:setPrivateProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/ConfigTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\ConfigTest\\:\\:\\$dsnGroup type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/ConfigTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\ConfigTest\\:\\:\\$dsnGroupPostgre type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/ConfigTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\ConfigTest\\:\\:\\$dsnGroupPostgreNative type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/ConfigTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\ConfigTest\\:\\:\\$group type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/ConfigTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\DatabaseTestCase\\\\DatabaseTestCaseMigrationOnce1Test\\:\\:\\$namespace type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/DatabaseTestCase/DatabaseTestCaseMigrationOnce1Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\DatabaseTestCase\\\\DatabaseTestCaseMigrationOnce2Test\\:\\:\\$namespace type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/DatabaseTestCase/DatabaseTestCaseMigrationOnce2Test.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type array\\|string of property CodeIgniter\\\\Database\\\\DatabaseTestCaseTest\\:\\:\\$seed is not the same as PHPDoc type array\\<int, class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\>\\>\\|class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\> of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$seed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/DatabaseTestCaseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\DatabaseTestCaseTest\\:\\:\\$namespace type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/DatabaseTestCaseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\DatabaseTestCaseTest\\:\\:\\$seed type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/DatabaseTestCaseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Live\\\\ConnectTest\\:\\:\\$group1 has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/ConnectTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Live\\\\ConnectTest\\:\\:\\$group2 has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/ConnectTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Live\\\\ConnectTest\\:\\:\\$tests has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/ConnectTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:escapeLikeStringDirect\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/EscapeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$name on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/FabricatorLiveTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$fields of method CodeIgniter\\\\Database\\\\Forge\\:\\:addField\\(\\) expects array\\<string, array\\|string\\>\\|string, array\\<int, string\\> given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Database/Live/ForgeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$currentRow on CodeIgniter\\\\Database\\\\ResultInterface\\|false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$name on array\\|object\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$resultID on CodeIgniter\\\\Database\\\\ResultInterface\\|false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Database/Live/GetTest\\.php\\:256\\:\\:\\$country has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Database/Live/GetTest\\.php\\:256\\:\\:\\$created_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Database/Live/GetTest\\.php\\:256\\:\\:\\$deleted_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Database/Live/GetTest\\.php\\:256\\:\\:\\$email has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Database/Live/GetTest\\.php\\:256\\:\\:\\$id has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Database/Live/GetTest\\.php\\:256\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Database/Live/GetTest\\.php\\:256\\:\\:\\$updated_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/GetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:getWhere\\(\\) with incorrect case\\: getwhere$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Database/Live/InsertTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\Live\\\\MetadataTest\\:\\:\\$seed is not the same as PHPDoc type array\\<int, class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\>\\>\\|class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\> of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$seed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/MetadataTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Live\\\\MetadataTest\\:\\:\\$expectedTables type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/MetadataTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$numberNative\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Database/Live/MySQLi/NumberNativeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Live\\\\MySQLi\\\\NumberNativeTest\\:\\:\\$tests has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/MySQLi/NumberNativeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Class stdClass referenced with incorrect case\\: stdclass\\.$#',
	'count' => 9,
	'path' => __DIR__ . '/tests/system/Database/Live/MySQLi/RawSqlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:getCursor\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/OCI8/CallStoredProcedureTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:storedProcedure\\(\\)\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Database/Live/OCI8/CallStoredProcedureTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$schema\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/OrderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$schema\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/PreparedQueryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$db of class CodeIgniter\\\\Database\\\\SQLite3\\\\Table constructor expects CodeIgniter\\\\Database\\\\SQLite3\\\\Connection, CodeIgniter\\\\Database\\\\BaseConnection given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/SQLite3/AlterTableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$forge of class CodeIgniter\\\\Database\\\\SQLite3\\\\Table constructor expects CodeIgniter\\\\Database\\\\SQLite3\\\\Forge, CodeIgniter\\\\Database\\\\Forge given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/SQLite3/AlterTableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Live\\\\SQLite3\\\\AlterTableTest\\:\\:\\$forge \\(CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\) does not accept CodeIgniter\\\\Database\\\\Forge\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/SQLite3/AlterTableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\SQLite3\\\\Connection of property CodeIgniter\\\\Database\\\\Live\\\\SQLite3\\\\GetIndexDataTest\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/SQLite3/GetIndexDataTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Live\\\\SQLite3\\\\GetIndexDataTest\\:\\:\\$db \\(CodeIgniter\\\\Database\\\\SQLite3\\\\Connection\\) does not accept CodeIgniter\\\\Database\\\\BaseConnection\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Database/Live/SQLite3/GetIndexDataTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Live\\\\SQLite3\\\\GetIndexDataTest\\:\\:\\$forge \\(CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\) does not accept CodeIgniter\\\\Database\\\\Forge\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/SQLite3/GetIndexDataTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Live\\\\UpdateTest\\:\\:provideUpdateBatch\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/UpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Live\\\\UpdateTest\\:\\:testUpdateBatch\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/UpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Live\\\\UpdateTest\\:\\:testUpdateBatch\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Live/UpdateTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:getWhere\\(\\) with incorrect case\\: getwhere$#',
	'count' => 9,
	'path' => __DIR__ . '/tests/system/Database/Live/UpsertTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Class stdClass referenced with incorrect case\\: stdclass\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/Database/Live/UpsertTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$set of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:updateFields\\(\\) expects array\\<int, CodeIgniter\\\\Database\\\\RawSql\\|string\\>\\|string, array\\<string, CodeIgniter\\\\Database\\\\RawSql\\> given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Database/Live/UpsertTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$schema\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Database/Migrations/MigrationRunnerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Migrations\\\\MigrationRunnerTest\\:\\:resetTables\\(\\) has parameter \\$db with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Migrations/MigrationRunnerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\Migrations\\\\MigrationRunnerTest\\:\\:\\$namespace type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Database/Migrations/MigrationRunnerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:pretend\\(\\)\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionHandlerTest\\:\\:backupIniValues\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Debug\\\\ExceptionHandlerTest\\:\\:\\$iniSettings type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'CODEIGNITER_SCREAM_DEPRECATIONS\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'1\' directly on offset \'CODEIGNITER_SCREAM_DEPRECATIONS\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function strlen\\(\\) on a separate line has no effect\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Instantiating CastException using new is not allowed\\. Use one of its named constructors instead\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionsTest\\:\\:getPrivateMethodInvoker\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\ExceptionsTest\\:\\:setPrivateProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/ExceptionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$callable of method CodeIgniter\\\\Debug\\\\Timer\\:\\:record\\(\\) expects callable\\(\\)\\: mixed, \'strlen\' given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Debug/TimerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Instantiating FrameworkException using new is not allowed\\. Use one of its named constructors instead\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/DebugTraceableTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\EmailTest\\:\\:provideEmailSendWithClearance\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Email/EmailTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Email/EmailTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Encryption\\\\EncrypterInterface\\:\\:\\$key\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Encryption/EncryptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Encryption\\\\Encryption\\:\\:\\$bogus\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Encryption/EncryptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\Encryption\\\\Encryption\\:\\:\\$digest\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Encryption/EncryptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\Encryption\\\\Encryption\\:\\:\\$key\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Encryption/EncryptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'encryption\\.key\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Encryption/EncryptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Encryption\\\\EncrypterInterface\\:\\:\\$cipher\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Encryption/Handlers/OpenSSLHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Encryption\\\\EncrypterInterface\\:\\:\\$key\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Encryption/Handlers/OpenSSLHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Encryption\\\\EncrypterInterface\\:\\:\\$blockSize\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Encryption/Handlers/SodiumHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Encryption\\\\EncrypterInterface\\:\\:\\$driver\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Encryption/Handlers/SodiumHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Encryption\\\\EncrypterInterface\\:\\:\\$key\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Encryption/Handlers/SodiumHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property object\\:\\:\\$bar\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method object\\:\\:toRawArray\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Expression "\\$entity\\-\\>ninth" on a separate line does not do anything\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getCastEntity\\(\\) has parameter \\$data with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getCastNullableEntity\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getCustomCastEntity\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getEntity\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getMappedEntity\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getNewSetterGetterEntity\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getPrivateMethodInvoker\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getSimpleSwappedEntity\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:getSwappedEntity\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Entity\\\\EntityTest\\:\\:setPrivateProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1078\\:\\:getBar\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1078\\:\\:getFakeBar\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1078\\:\\:setBar\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1078\\:\\:setBar\\(\\) has parameter \\$value with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1116\\:\\:_getBar\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1116\\:\\:_setBar\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1116\\:\\:_setBar\\(\\) has parameter \\$value with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1116\\:\\:getBar\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1116\\:\\:setBar\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1116\\:\\:setBar\\(\\) has parameter \\$value with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1162\\:\\:getSimple\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/Entity/EntityTest\\.php\\:1229\\:\\:setSeventh\\(\\) has parameter \\$seventh with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1078\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1078\\:\\:\\$original type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1116\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1116\\:\\:\\$original type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1162\\:\\:\\$_original has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1162\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1192\\:\\:\\$_original has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1192\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1211\\:\\:\\$_original has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1211\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1229\\:\\:\\$_original has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1229\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1287\\:\\:\\$_original has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1287\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1316\\:\\:\\$_original has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:1316\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Entity/EntityTest\\.php\\:895\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Entity/EntityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument \\#1 \\$name \\(\'Modules\'\\) passed to function config does not extend CodeIgniter\\\\\\\\Config\\\\\\\\BaseConfig\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Events/EventsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Events\\\\Events\\:\\:unInitialize\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Events/EventsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Filters\\\\CSRFTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\|null\\) does not accept CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Filters/CSRFTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/DebugToolbarTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Filters\\\\DebugToolbarTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\) does not accept CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/DebugToolbarTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'DELETE\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 36,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 2 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'spark\', \'list\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\FiltersTest\\:\\:createFilters\\(\\) has parameter \\$request with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\FiltersTest\\:\\:provideBeforeExcept\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\FiltersTest\\:\\:provideProcessMethodProcessGlobalsWithExcept\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\FiltersTest\\:\\:testBeforeExcept\\(\\) has parameter \\$except with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\FiltersTest\\:\\:testBeforeExcept\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\FiltersTest\\:\\:testProcessMethodProcessGlobalsWithExcept\\(\\) has parameter \\$except with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Filters\\\\FiltersTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\) does not accept CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/FiltersTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/HoneypotTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Filters\\\\HoneypotTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\|null\\) does not accept CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Filters/HoneypotTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Filters\\\\HoneypotTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\|null\\) does not accept CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Filters/HoneypotTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'val\' of \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Filters/InvalidCharsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\InvalidCharsTest\\:\\:provideCheckControlStringWithControlCharsCausesException\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/InvalidCharsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\InvalidCharsTest\\:\\:provideCheckControlStringWithLineBreakAndTabReturnsTheString\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/InvalidCharsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Filters/InvalidCharsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\fixtures\\\\InvalidClass\\:\\:index\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Filters/fixtures/InvalidClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with incorrect case\\: assertInstanceof$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Format/FormatTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Format\\\\XMLFormatterTest\\:\\:provideValidatingInvalidTags\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Format/XMLFormatterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Format\\\\XMLFormatterTest\\:\\:testValidatingInvalidTags\\(\\) has parameter \\$input with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Format/XMLFormatterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'baz\' directly on offset \'bar\' of \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'abc \\< def\', \'McDonald\\\\\'s\', \'\\<s\\>aaa\\</s\\>\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'pro\\-file\', \'\\-\\-foo\', \'bar\', \'\\-\\-baz\', \'queue some stuff\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'profile\', \'\\-\\-foo\', \'bar\', \'\\-\\-baz\', \'queue some stuff\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'profile\', \'\\-\\-foo\', \'bar\', \'\\-\\-foo\\-bar\', \'yes\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'profile\', \'\\-\\-foo\', \'bar\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'profile\', \'\\-\\-foo\', \'oops\', \'bar\', \'\\-\\-baz\', \'queue some stuff\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'profile\', \'\\-\\-foo\', \'oops\\-bar\', \'\\-\\-baz\', \'queue some stuff\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'profile\', \'\\-foo\', \'bar\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'users\', \'21\', \'profile\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'spark\', \'command\', \'param1\', \'param2\', \'\\-\\-opt1\', \'opt1val\', \'\\-\\-opt\\-2\', \'opt 2 val\', \'param3\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CLIRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'10\' directly on offset \'HTTP_CONTENT_LENGTH\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CURLRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'en\\-US\' directly on offset \'HTTP_ACCEPT_LANGUAGE\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CURLRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'gzip, deflate, br\' directly on offset \'HTTP_ACCEPT_ENCODING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CURLRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'site1\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/CURLRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicyTest\\:\\:work\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ContentSecurityPolicyTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_USER_AGENT\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/DownloadResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'Mozilla/5\\.0 \\(Linux; U; Android 2\\.0\\.3; ja\\-jp; SC\\-02C Build/IML74K\\) AppleWebKit/534\\.30 \\(KHTML, like Gecko\\) Version/4\\.0 Mobile Safari/534\\.30\' directly on offset \'HTTP_USER_AGENT\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/DownloadResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNull\\(\\) with incorrect case\\: AssertNull$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/Files/FileCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Function CodeIgniter\\\\HTTP\\\\Files\\\\is_uploaded_file\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/Files/FileMovingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Function CodeIgniter\\\\HTTP\\\\Files\\\\is_uploaded_file\\(\\) has parameter \\$filename with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/Files/FileMovingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Function CodeIgniter\\\\HTTP\\\\Files\\\\move_uploaded_file\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/Files/FileMovingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Function CodeIgniter\\\\HTTP\\\\Files\\\\move_uploaded_file\\(\\) has parameter \\$destination with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/Files/FileMovingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Function CodeIgniter\\\\HTTP\\\\Files\\\\move_uploaded_file\\(\\) has parameter \\$filename with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/Files/FileMovingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Function CodeIgniter\\\\HTTP\\\\Files\\\\rrmdir\\(\\) has parameter \\$src with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/Files/FileMovingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$value of class CodeIgniter\\\\HTTP\\\\Header constructor expects array\\<int\\|string, array\\<string, string\\>\\|string\\>\\|string\\|null, int given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/HeaderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$value of class CodeIgniter\\\\HTTP\\\\Header constructor expects array\\<int\\|string, array\\<string, string\\>\\|string\\>\\|string\\|null, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/HeaderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'\' directly on offset \'QUERY_STRING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/\\?/ci/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/index\\.php/popcorn/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/woot\' directly on offset \'QUERY_STRING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/woot\\?code\\=good\' directly on offset \'QUERY_STRING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 11,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php/woot\\?code\\=good\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\\?\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\\?/ci/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\\?/ci/woot\\?code\\=good\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/sub/example\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/sub/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestDetectingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'10\\.0\\.1\\.200\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'10\\.10\\.1\\.200\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'123\\.123\\.123\\.123\' directly on offset \'HTTP_X_FORWARDED_FOR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 7,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'123\\.123\\.123\\.123\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'123\\.456\\.23\\.123\' directly on offset \'HTTP_X_FORWARDED_FOR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'192\\.168\\.5\\.21\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'2001\\:db8\\:1234\\:ffff\\:ffff\\:ffff\\:ffff\\:ffff\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'2001\\:db8\\:1235\\:ffff\\:ffff\\:ffff\\:ffff\\:ffff\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'2001\\:db8\\:\\:2\\:1\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'2001\\:db8\\:\\:2\\:2\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'2001\\:xyz\\:\\:1\' directly on offset \'HTTP_X_FORWARDED_FOR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'3\' directly on offset \'TEST\' of \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'3\' directly on offset \'get\' of \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'Mozilla\' directly on offset \'HTTP_USER_AGENT\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'fr\\-FR; q\\=1\\.0, en; q\\=0\\.5\' directly on offset \'HTTP_ACCEPT_LANGUAGE\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'fr; q\\=1\\.0, en; q\\=0\\.5\' directly on offset \'HTTP_ACCEPT_LANGUAGE\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'on\' directly on offset \'HTTPS\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 3 directly on offset \'TEST\' of \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 5 directly on offset \'TEST\' of \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning mixed directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning mixed directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getCookie\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getDefaultLocale\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getFile\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getFileMultiple\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getFiles\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getGet\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getGetPost\\(\\)\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getLocale\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getOldInput\\(\\)\\.$#',
	'count' => 9,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getPost\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getPostGet\\(\\)\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getVar\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:is\\(\\)\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:isAJAX\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:isCLI\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:isSecure\\(\\)\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:negotiate\\(\\)\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequestTest\\:\\:createRequest\\(\\) has parameter \\$body with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequestTest\\:\\:provideCanGrabGetRawInputVar\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequestTest\\:\\:provideExtensionPHP\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequestTest\\:\\:provideIsHTTPMethods\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Config\\\\App\\:\\:\\$proxyIPs \\(array\\<string, string\\>\\) does not accept array\\<int, string\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Config\\\\App\\:\\:\\$proxyIPs \\(array\\<string, string\\>\\) does not accept string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/IncomingRequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\MessageTest\\:\\:provideArrayHeaderValue\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/MessageTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\MessageTest\\:\\:testSetHeaderWithExistingArrayValuesAppendArrayValue\\(\\) has parameter \\$arrayHeaderValue with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/MessageTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\MessageTest\\:\\:testSetHeaderWithExistingArrayValuesAppendStringValue\\(\\) has parameter \\$arrayHeaderValue with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/MessageTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Log\\\\Logger constructor expects Config\\\\Logger, CodeIgniter\\\\Test\\\\Mock\\\\MockLogger given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RedirectExceptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RedirectResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'http\\://somewhere\\.com\' directly on offset \'HTTP_REFERER\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RedirectResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Router\\\\RouteCollection of property CodeIgniter\\\\HTTP\\\\RedirectResponseTest\\:\\:\\$routes is not the same as PHPDoc type CodeIgniter\\\\Router\\\\RouteCollection\\|null of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$routes\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RedirectResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RedirectResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'10\\.0\\.1\\.200\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'10\\.10\\.1\\.200\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'123\\.123\\.123\\.123\' directly on offset \'HTTP_X_FORWARDED_FOR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/HTTP/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'123\\.123\\.123\\.123\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'123\\.456\\.23\\.123\' directly on offset \'HTTP_X_FORWARDED_FOR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'192\\.168\\.5\\.21\' directly on offset \'REMOTE_ADDR\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'baz\' directly on offset \'bar\' of \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ResponseCookieTest\\:\\:\\$defaults type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseCookieTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$expire of method CodeIgniter\\\\HTTP\\\\Response\\:\\:setCookie\\(\\) expects int, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseSendTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'SERVER_SOFTWARE\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'HTTP/1\\.1\' directly on offset \'SERVER_PROTOCOL\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'Microsoft\\-IIS\' directly on offset \'SERVER_SOFTWARE\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'SERVER_PROTOCOL\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'SERVER_SOFTWARE\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ResponseTest\\:\\:provideRedirect\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ResponseTest\\:\\:provideRedirectWithIIS\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$data of method CodeIgniter\\\\HTTP\\\\Message\\:\\:setBody\\(\\) expects string, array\\<string, array\\<int, int\\>\\|string\\> given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$expire of method CodeIgniter\\\\HTTP\\\\Response\\:\\:setCookie\\(\\) expects int, string given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\ResponseTest\\:\\:\\$server type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/ResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'QUERY_STRING\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'\' directly on offset \'/ci/woot\' of \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/\\?/ci/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/candy/snickers\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/index\\.php/popcorn/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/woot\' directly on offset \'QUERY_STRING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/woot\\?code\\=good\' directly on offset \'QUERY_STRING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci431/public/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci431/public/index\\.php/woot\\?code\\=good\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/fruits/banana\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 13,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php/fruits/banana\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php/woot\\?code\\=good\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\\?\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\\?/ci/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\\?/ci/woot\\?code\\=good\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/sub/example\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/sub/folder/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/sub/folder/index\\.php/fruits/banana\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/sub/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/woot\' directly on offset \'PATH_INFO\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/woot\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'good\' directly on offset \'/ci/woot\\?code\' of \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURIFactoryDetectRoutePathTest\\:\\:createSiteURIFactory\\(\\) has parameter \\$server with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURIFactoryDetectRoutePathTest\\:\\:provideExtensionPHP\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryDetectRoutePathTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php/woot\\?code\\=good\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/woot\' directly on offset \'PATH_INFO\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'code\\=good\' directly on offset \'QUERY_STRING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'good\' directly on offset \'code\' of \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'localhost\\:8080\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'users\\.example\\.jp\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURIFactoryTest\\:\\:provideCreateFromStringWithIndexPage\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURIFactoryTest\\:\\:provideCreateFromStringWithoutIndexPage\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURIFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURITest\\:\\:provideConstructor\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURITest\\:\\:provideRelativePathWithQueryOrFragment\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURITest\\:\\:provideSetPath\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURITest\\:\\:testConstructor\\(\\) has parameter \\$expectedSegments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\SiteURITest\\:\\:testSetPath\\(\\) has parameter \\$expectedSegments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#4 \\$scheme of class CodeIgniter\\\\HTTP\\\\SiteURI constructor expects \'http\'\\|\'https\'\\|null, \'\' given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/SiteURITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'\' directly on offset \'QUERY_STRING\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/v4/controller/method\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/v4/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/v4/index\\.php/controller/method\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/controller/method\' directly on offset \'PATH_INFO\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\URI\\:\\:getRoutePath\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URITest\\:\\:defaultResolutions\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URITest\\:\\:provideAuthorityRemovesDefaultPorts\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URITest\\:\\:provideAuthorityReturnsExceptedValues\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URITest\\:\\:providePathGetsFiltered\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URITest\\:\\:provideRemoveDotSegments\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URITest\\:\\:provideSetPath\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URITest\\:\\:provideSimpleUri\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/URITest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_REFERER\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/UserAgentTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_USER_AGENT\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/UserAgentTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'http\\://codeigniter\\.com/user_guide/\' directly on offset \'HTTP_REFERER\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HTTP/UserAgentTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'HTTP_USER_AGENT\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/HTTP/UserAgentTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelperDotKeyExistsTest\\:\\:\\$array type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/Array/ArrayHelperDotKeyExistsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelperRecursiveDiffTest\\:\\:\\$compareWith type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/Array/ArrayHelperRecursiveDiffTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelperSortValuesByNaturalTest\\:\\:\\$arrayWithArrayValues type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/Array/ArrayHelperSortValuesByNaturalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Helpers\\\\Array\\\\ArrayHelperSortValuesByNaturalTest\\:\\:\\$arrayWithStringValues type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/Array/ArrayHelperSortValuesByNaturalTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:provideArrayDeepSearch\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:provideArrayFlattening\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:provideArrayGroupByExcludeEmpty\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:provideArrayGroupByIncludeEmpty\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:provideSortByMultipleKeys\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayDeepSearch\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayFlattening\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayFlattening\\(\\) has parameter \\$input with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayGroupByExcludeEmpty\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayGroupByExcludeEmpty\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayGroupByExcludeEmpty\\(\\) has parameter \\$indexes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayGroupByIncludeEmpty\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayGroupByIncludeEmpty\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArrayGroupByIncludeEmpty\\(\\) has parameter \\$indexes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysFailsEmptyParameter\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysFailsEmptyParameter\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysFailsEmptyParameter\\(\\) has parameter \\$sortColumns with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysWithArray\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysWithArray\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysWithArray\\(\\) has parameter \\$sortColumns with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysWithObjects\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysWithObjects\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\ArrayHelperTest\\:\\:testArraySortByMultipleKeysWithObjects\\(\\) has parameter \\$sortColumns with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Helpers\\\\CookieHelperTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\) does not accept CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/CookieHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method org\\\\bovigo\\\\vfs\\\\visitor\\\\vfsStreamVisitor\\:\\:getStructure\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Helpers/FilesystemHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @var for property CodeIgniter\\\\Helpers\\\\FilesystemHelperTest\\:\\:\\$structure with type mixed is not subtype of native type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/FilesystemHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Helpers\\\\FilesystemHelperTest\\:\\:\\$structure type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/FilesystemHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$value of function form_hidden expects array\\|string, null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/FormHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Helpers\\\\HTMLHelperTest\\:\\:\\$tracks type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/HTMLHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\InflectorHelperTest\\:\\:provideOrdinal\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/InflectorHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$num of function number_to_size expects int\\|string, float given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/NumberHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/assets/image\\.jpg\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/foo/public/bar\\?baz\\=quip\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/foo/public/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/public/\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/public/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/subfolder/assets/image\\.jpg\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/subfolder/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'8080\' directly on offset \'SERVER_PORT\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 11,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'invalid\\.example\\.org\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'www\\.example\\.jp\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning non\\-falsy\\-string directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\CurrentUrlTest\\:\\:createRequest\\(\\) has parameter \\$body with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\CurrentUrlTest\\:\\:provideUrlIs\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/CurrentUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'http\\://example\\.com/one\\?two\' directly on offset \'HTTP_REFERER\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAnchor\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAnchorExamples\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAnchorNoindex\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAnchorPopup\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAnchorTargetted\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAutoLinkEmail\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAutoLinkPopup\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAutoLinkUrl\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideAutolinkBoth\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideMailto\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:providePrepUrl\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideSafeMailto\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideUrlTo\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:provideUrlToThrowsOnEmptyOrMissingRoute\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\MiscUrlTest\\:\\:testUrlTo\\(\\) has parameter \\$args with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/MiscUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\SiteUrlCliTest\\:\\:provideUrls\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlCliTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_HOST\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'REQUEST_URI\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/v4/x/y\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/public\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/public/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/test\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/test/page\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'www\\.example\\.jp\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\SiteUrlTest\\:\\:createRequest\\(\\) has parameter \\$body with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Helpers\\\\URLHelper\\\\SiteUrlTest\\:\\:provideUrls\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Helpers/URLHelper/SiteUrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'test\' directly on offset \'HTTPS\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:call\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:delete\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:options\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:patch\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:populateGlobals\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:post\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:put\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:setRequestBody\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:withHeaders\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:withRoutes\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HomeTest\\:\\:withSession\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, CodeIgniter\\\\Router\\\\RouteCollection\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$request of method CodeIgniter\\\\CodeIgniter\\:\\:setRequest\\(\\) expects CodeIgniter\\\\HTTP\\\\CLIRequest\\|CodeIgniter\\\\HTTP\\\\IncomingRequest, CodeIgniter\\\\HTTP\\\\Request given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$params of method CodeIgniter\\\\HomeTest\\:\\:populateGlobals\\(\\) expects non\\-empty\\-array\\|null, array\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$session \\(array\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/HomeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Honeypot/HoneypotTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Filters\\\\Filters constructor expects Config\\\\Filters, object\\{aliases\\: array\\<string, string\\>, globals\\: array\\<string, array\\<int, string\\>\\>\\}&stdClass given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Honeypot/HoneypotTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$data of method CodeIgniter\\\\HTTP\\\\Message\\:\\:setBody\\(\\) expects string, null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Honeypot/HoneypotTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Honeypot\\\\HoneypotTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\) does not accept CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Honeypot/HoneypotTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Honeypot\\\\HoneypotTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\Response\\) does not accept CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Honeypot/HoneypotTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$nonsense\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$days\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$hours\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$minutes\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$months\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$seconds\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$weeks\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to protected property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$years\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\I18n\\\\TimeDifference\\:\\:\\$days \\(int\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeDifferenceTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\I18n\\\\TimeLegacy\\:\\:\\$timezoneName\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeLegacyTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\I18n\\\\TimeLegacy\\:\\:\\$weekOfWeek\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeLegacyTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\I18n\\\\TimeLegacyTest\\:\\:provideToStringDoesNotDependOnLocale\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeLegacyTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\I18n\\\\Time\\:\\:\\$timezoneName\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\I18n\\\\Time\\:\\:\\$weekOfWeek\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\I18n\\\\TimeTest\\:\\:provideToStringDoesNotDependOnLocale\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/I18n/TimeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:getPathname\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Images/BaseHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method org\\\\bovigo\\\\vfs\\\\vfsStreamContent\\:\\:getContent\\(\\)\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Images/GDHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$image of function imagecolorat expects GdImage, resource given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Images/GDHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$image of function imagecolorsforindex expects GdImage, resource given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Images/GDHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$filename of function file_get_contents expects string, resource given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Images/ImageMagickHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Language\\\\Language\\:\\:disableIntlSupport\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Language/LanguageTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Language\\\\Language\\:\\:loaded\\(\\)\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Language/LanguageTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Language\\\\Language\\:\\:loadem\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Language/LanguageTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Language\\\\Language\\:\\:setData\\(\\)\\.$#',
	'count' => 9,
	'path' => __DIR__ . '/tests/system/Language/LanguageTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Language\\\\LanguageTest\\:\\:provideBundleUniqueKeys\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Language/LanguageTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$message of method CodeIgniter\\\\Log\\\\Handlers\\\\ChromeLoggerHandler\\:\\:handle\\(\\) expects string, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Log/Handlers/ChromeLoggerHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Handlers\\\\ErrorlogHandlerTest\\:\\:getMockedHandler\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Log/Handlers/ErrorlogHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Log\\\\Logger constructor expects Config\\\\Logger, CodeIgniter\\\\Test\\\\Mock\\\\MockLogger given\\.$#',
	'count' => 24,
	'path' => __DIR__ . '/tests/system/Log/LoggerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$level of method CodeIgniter\\\\Log\\\\Logger\\:\\:log\\(\\) expects string, int given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Log/LoggerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$message of method CodeIgniter\\\\Log\\\\Logger\\:\\:log\\(\\) expects string\\|Stringable, CodeIgniter\\\\Test\\\\Mock\\\\MockLogger given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Log/LoggerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Log/LoggerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Result of method CodeIgniter\\\\Log\\\\Logger\\:\\:log\\(\\) \\(void\\) is used\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Log/LoggerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Model\\:\\:affectedRows\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/AffectedRowsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'email\' does not exist on array\\<int, array\\{\\}\\>\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Models/DataConverterModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'email\' does not exist on array\\{\\}\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Models/DataConverterModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'id\' does not exist on array\\{email\\: array\\{\'private@example\\.org\'\\}\\}\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/DataConverterModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'id\' does not exist on array\\{\\}\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Models/DataConverterModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$row of method CodeIgniter\\\\BaseModel\\:\\:save\\(\\) expects array\\<int\\|string, float\\|int\\|object\\|string\\|null\\>\\|object, array\\<string, array\\<int, string\\>\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/DataConverterModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$row of method CodeIgniter\\\\Model\\:\\:update\\(\\) expects array\\<int\\|string, float\\|int\\|object\\|string\\|null\\>\\|object\\|null, array\\<string, array\\<int, string\\>\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/DataConverterModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Models\\\\DataConverterModelTest\\:\\:\\$seed \\(array\\<int, class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\>\\>\\|class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\>\\) does not accept default value of type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/DataConverterModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Models\\\\DeleteModelTest\\:\\:emptyPkValues\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/DeleteModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type Tests\\\\Support\\\\Models\\\\EventModel of property CodeIgniter\\\\Models\\\\EventsModelTest\\:\\:\\$model is not the same as PHPDoc type CodeIgniter\\\\Model of overridden property CodeIgniter\\\\Models\\\\LiveModelTestCase\\:\\:\\$model\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/EventsModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Model\\:\\:getLastQuery\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$name on array\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$total on array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Models\\\\FindModelTest\\:\\:provideAggregateAndGroupBy\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Models\\\\FindModelTest\\:\\:provideFirstAggregate\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, mixed given\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in \\|\\|, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/FindModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property object\\:\\:\\$charset\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/GeneralModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Model\\:\\:undefinedMethodCall\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/GeneralModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$country on array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$created_at on array\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:193\\:\\:\\$_options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:193\\:\\:\\$country has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:193\\:\\:\\$created_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:193\\:\\:\\$deleted has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:193\\:\\:\\$email has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:193\\:\\:\\$id has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:193\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:193\\:\\:\\$updated_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:287\\:\\:\\$_options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:287\\:\\:\\$country has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:287\\:\\:\\$created_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:287\\:\\:\\$deleted has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:287\\:\\:\\$email has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:287\\:\\:\\$id has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:287\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/InsertModelTest\\.php\\:287\\:\\:\\$updated_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/InsertModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Models\\\\LiveModelTestCase\\:\\:getPrivateMethodInvoker\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/LiveModelTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Models\\\\LiveModelTestCase\\:\\:setPrivateProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/LiveModelTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$created_at on array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/MiscellaneousModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/PaginateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method class@anonymous/tests/system/Models/SaveModelTest\\.php\\:288\\:\\:truncate\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$description on array\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$name on array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:239\\:\\:\\$_options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:239\\:\\:\\$country has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:239\\:\\:\\$created_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:239\\:\\:\\$deleted has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:239\\:\\:\\$email has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:239\\:\\:\\$id has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:239\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:239\\:\\:\\$updated_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:272\\:\\:\\$_options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:272\\:\\:\\$created_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:272\\:\\:\\$id has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:272\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:272\\:\\:\\$updated_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/SaveModelTest\\.php\\:288\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/SaveModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$country on array\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$created_at on array\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$updated_at on array\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Models\\\\TimestampModelTest\\:\\:allowDatesPrepareOneRecord\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Models\\\\TimestampModelTest\\:\\:doNotAllowDatesPrepareOneRecord\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'created_at\' does not exist on array\\{\\}\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'id\' does not exist on array\\{country\\: \'CA\'\\}\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Models\\\\TimestampModelTest\\:\\:\\$seed \\(array\\<int, class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\>\\>\\|class\\-string\\<CodeIgniter\\\\Database\\\\Seeder\\>\\) does not accept default value of type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/TimestampModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$value on array\\<int, array\\>\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Models\\\\UpdateModelTest\\:\\:provideUpdateThrowDatabaseExceptionWithoutWhereClause\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$id of method CodeIgniter\\\\Model\\:\\:update\\(\\) expects array\\|int\\|string\\|null, false\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$row of method CodeIgniter\\\\BaseModel\\:\\:save\\(\\) expects array\\<int\\|string, float\\|int\\|object\\|string\\|null\\>\\|object, array\\<int, array\\>\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:197\\:\\:\\$_options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:197\\:\\:\\$country has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:197\\:\\:\\$created_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:197\\:\\:\\$deleted has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:197\\:\\:\\$email has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:197\\:\\:\\$id has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:197\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:197\\:\\:\\$updated_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:216\\:\\:\\$_options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:216\\:\\:\\$country has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:216\\:\\:\\$created_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:216\\:\\:\\$deleted has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:216\\:\\:\\$email has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:216\\:\\:\\$id has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:216\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:216\\:\\:\\$updated_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:349\\:\\:\\$_options has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:349\\:\\:\\$country has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:349\\:\\:\\$created_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:349\\:\\:\\$deleted has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:349\\:\\:\\$email has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:349\\:\\:\\$id has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:349\\:\\:\\$name has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/UpdateModelTest\\.php\\:349\\:\\:\\$updated_at has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unreachable statement \\- code above always terminates\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/UpdateModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/Models/ValidationModelTest\\.php\\:245\\:\\:\\$grouptest has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/ValidationModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$key on array\\.$#',
	'count' => 7,
	'path' => __DIR__ . '/tests/system/Models/WhenWhenNotModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$value on array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Models/WhenWhenNotModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'page\' directly on \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'page_foo\' directly on \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/v4/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/ci/v4/x/y\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/index\\.php\' directly on offset \'SCRIPT_NAME\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'bar\' directly on offset \'foo\' of \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 2 directly on offset \'page\' of \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 2 directly on offset \'page_foo\' of \\$_GET is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 3 directly on offset \'page\' of \\$_GET is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Re\\-assigning arrays to \\$_GET directly is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/Pager/PagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Publisher\\\\PublisherOutputTest\\:\\:\\$structure type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Publisher/PublisherOutputTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Publisher\\\\PublisherRestrictionsTest\\:\\:provideDefaultPublicRestrictions\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Publisher/PublisherRestrictionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Publisher\\\\PublisherRestrictionsTest\\:\\:provideDestinations\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Publisher/PublisherRestrictionsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Publisher/PublisherSupportTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/1\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/1/edit\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/123\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/new\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'DELETE\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'HTTP/1\\.1\' directly on offset \'SERVER_PROTOCOL\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'PATCH\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'PUT\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 2 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 3 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 4 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'1\', \'edit\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'1\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'123\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'new\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:pretend\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\ResourceControllerTest\\:\\:invoke\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\ResourceControllerTest\\:\\:invoke\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Router\\\\RouteCollection of property CodeIgniter\\\\RESTful\\\\ResourceControllerTest\\:\\:\\$routes is not the same as PHPDoc type CodeIgniter\\\\Router\\\\RouteCollection\\|null of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$routes\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$format of method CodeIgniter\\\\RESTful\\\\ResourceController\\:\\:setFormat\\(\\) expects \'json\'\\|\'xml\', \'Nonsense\' given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourceControllerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/create\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/delete/123\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/edit/1\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/new\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/remove/123\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/show/1\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'/work/update/123\' directly on offset \'REQUEST_URI\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'HTTP/1\\.1\' directly on offset \'SERVER_PROTOCOL\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 2 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 3 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning 4 directly on offset \'argc\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'create\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'delete\', \'123\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'edit\', \'1\', \'edit\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'new\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'remove\', \'123\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'show\', \'1\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\', \'update\', \'123\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning array\\{\'index\\.php\', \'work\'\\} directly on offset \'argv\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Router\\\\RouteCollection of property CodeIgniter\\\\RESTful\\\\ResourcePresenterTest\\:\\:\\$routes is not the same as PHPDoc type CodeIgniter\\\\Router\\\\RouteCollection\\|null of overridden property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$routes\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/RESTful/ResourcePresenterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouterImprovedTest\\:\\:createNewAutoRouter\\(\\) has parameter \\$namespace with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/AutoRouterImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouterImprovedTest\\:\\:provideRejectTranslateUriToCamelCase\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/AutoRouterImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouterImprovedTest\\:\\:provideTranslateUriToCamelCase\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/AutoRouterImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Controllers\\\\BlogController\\:\\:getSomeMethod\\(\\) has parameter \\$first with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/Controllers/BlogController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Controllers\\\\Index\\:\\:getIndex\\(\\) has parameter \\$p1 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/Controllers/Index.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Controllers\\\\Mycontroller\\:\\:getSomemethod\\(\\) has parameter \\$first with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/Controllers/Mycontroller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Controllers\\\\Remap\\:\\:_remap\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/Controllers/Remap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Controllers\\\\SubDir\\\\BlogController\\:\\:getSomeMethod\\(\\) has parameter \\$first with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/Controllers/SubDir/BlogController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Controllers\\\\Subfolder\\\\Home\\:\\:getIndex\\(\\) has parameter \\$p1 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/Controllers/Subfolder/Home.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Controllers\\\\Subfolder\\\\Home\\:\\:getIndex\\(\\) has parameter \\$p2 with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/Controllers/Subfolder/Home.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Controllers\\\\Subfolder\\\\Sub\\\\BlogController\\:\\:getSomeMethod\\(\\) has parameter \\$first with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/Controllers/Subfolder/Sub/BlogController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\DefinedRouteCollectorTest\\:\\:createRouteCollection\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/DefinedRouteCollectorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\DefinedRouteCollectorTest\\:\\:createRouteCollection\\(\\) has parameter \\$moduleConfig with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/DefinedRouteCollectorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionReverseRouteTest\\:\\:getCollector\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionReverseRouteTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionReverseRouteTest\\:\\:getCollector\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionReverseRouteTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionReverseRouteTest\\:\\:getCollector\\(\\) has parameter \\$files with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionReverseRouteTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionReverseRouteTest\\:\\:getCollector\\(\\) has parameter \\$moduleConfig with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionReverseRouteTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionReverseRouteTest\\:\\:provideReverseRoutingDefaultNamespaceAppController\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionReverseRouteTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'adm\\.example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'dev\\.example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'doc\\.domain\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'doc\\.example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'example\\.co\\.uk\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'www\\.example\\.com\' directly on offset \'HTTP_HOST\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Class CodeIgniter\\\\Controller referenced with incorrect case\\: CodeIgniter\\\\controller\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:getCollector\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:getCollector\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:getCollector\\(\\) has parameter \\$files with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:getCollector\\(\\) has parameter \\$moduleConfig with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:provideNestedGroupingWorksWithRootPrefix\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:provideRouteDefaultNamespace\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:provideRoutesOptionsWithSameFromTwoRoutes\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:testNestedGroupingWorksWithRootPrefix\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:testRoutesOptionsWithSameFromTwoRoutes\\(\\) has parameter \\$options1 with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionTest\\:\\:testRoutesOptionsWithSameFromTwoRoutes\\(\\) has parameter \\$options2 with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouteCollectionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouterTest\\:\\:provideRedirectRoute\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Router/RouterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFCookieRandomizeTokenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Config\\\\BaseConfig\\:\\:\\$regenerate\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionRandomizeTokenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Config\\\\BaseConfig\\:\\:\\$tokenRandomize\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionRandomizeTokenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 10,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionRandomizeTokenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'PUT\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionRandomizeTokenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\SecurityCSRFSessionRandomizeTokenTest\\:\\:createSession\\(\\) has parameter \\$options with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionRandomizeTokenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Test\\\\Mock\\\\MockSecurity constructor expects Config\\\\Security, CodeIgniter\\\\Config\\\\BaseConfig\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionRandomizeTokenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Config\\\\BaseConfig\\:\\:\\$regenerate\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'PUT\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\SecurityCSRFSessionTest\\:\\:createSession\\(\\) has parameter \\$options with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Security/SecurityCSRFSessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'GET\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Security/SecurityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'POST\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 9,
	'path' => __DIR__ . '/tests/system/Security/SecurityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'PUT\' directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Security/SecurityTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\AbstractHandlerTestCase\\:\\:getInstance\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/AbstractHandlerTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\AbstractHandlerTestCase\\:\\:getInstance\\(\\) has parameter \\$options with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/AbstractHandlerTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\AbstractHandlerTestCase\\:\\:getPrivateMethodInvoker\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/AbstractHandlerTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\AbstractHandlerTestCase\\:\\:setPrivateProperty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/AbstractHandlerTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\MySQLiHandlerTest\\:\\:getInstance\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/MySQLiHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\MySQLiHandlerTest\\:\\:getInstance\\(\\) has parameter \\$options with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/MySQLiHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\PostgreHandlerTest\\:\\:getInstance\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/PostgreHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\PostgreHandlerTest\\:\\:getInstance\\(\\) has parameter \\$options with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/PostgreHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\RedisHandlerTest\\:\\:getInstance\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/RedisHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\RedisHandlerTest\\:\\:getInstance\\(\\) has parameter \\$options with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/RedisHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\RedisHandlerTest\\:\\:provideSetSavePath\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/RedisHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\RedisHandlerTest\\:\\:testSetSavePath\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/Handlers/Database/RedisHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'xmlhttprequest\' directly on offset \'HTTP_X_REQUESTED_WITH\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionTest\\:\\:getInstance\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionTest\\:\\:getInstance\\(\\) has parameter \\$options with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Config\\\\Cookie\\:\\:\\$samesite \\(\'\'\\|\'Lax\'\\|\'None\'\\|\'Strict\'\\) does not accept \'Invalid\'\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\BootstrapFCPATHTest\\:\\:correctFCPATH\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/BootstrapFCPATHTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\BootstrapFCPATHTest\\:\\:fileContents\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/BootstrapFCPATHTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\BootstrapFCPATHTest\\:\\:readOutput\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/BootstrapFCPATHTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\BootstrapFCPATHTest\\:\\:readOutput\\(\\) has parameter \\$file with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/BootstrapFCPATHTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Test\\\\TestResponse\\:\\:ohno\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method CodeIgniter\\\\Test\\\\ControllerTestTraitTest\\:\\:withUri\\(\\) with incorrect case\\: withURI$#',
	'count' => 17,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Class App\\\\Controllers\\\\NeverHeardOfIt not found\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\ControllerTestTraitTest\\:\\:execute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Log\\\\Logger constructor expects Config\\\\Logger, CodeIgniter\\\\Test\\\\Mock\\\\MockLogger given\\.$#',
	'count' => 15,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\ControllerTestTraitTest\\:\\:\\$appConfig \\(Config\\\\App\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\ControllerTestTraitTest\\:\\:\\$logger \\(Psr\\\\Log\\\\LoggerInterface\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\ControllerTestTraitTest\\:\\:\\$request \\(CodeIgniter\\\\HTTP\\\\IncomingRequest\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\ControllerTestTraitTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\ControllerTestTraitTest\\:\\:\\$uri \\(string\\) does not accept CodeIgniter\\\\HTTP\\\\SiteURI\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Test/ControllerTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\DOMParserTest\\:\\:provideText\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/DOMParserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$created_at on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FabricatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$deleted_at on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FabricatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FabricatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$updated_at on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FabricatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\FabricatorTest\\:\\:\\$formatters type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FabricatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'test\' directly on offset \'HTTPS\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:call\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:delete\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:options\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:patch\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:populateGlobals\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:post\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:put\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:setRequestBody\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:withHeaders\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:withRoutes\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:withSession\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, CodeIgniter\\\\Router\\\\RouteCollection\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$request of method CodeIgniter\\\\CodeIgniter\\:\\:setRequest\\(\\) expects CodeIgniter\\\\HTTP\\\\CLIRequest\\|CodeIgniter\\\\HTTP\\\\IncomingRequest, CodeIgniter\\\\HTTP\\\\Request given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$params of method CodeIgniter\\\\Test\\\\FeatureTestAutoRoutingImprovedTest\\:\\:populateGlobals\\(\\) expects non\\-empty\\-array\\|null, array\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$session \\(array\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestAutoRoutingImprovedTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'test\' directly on offset \'HTTPS\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:call\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:delete\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:get\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:options\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:patch\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:populateGlobals\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:post\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:provideOpenCliRoutesFromHttpGot404\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:put\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:setRequestBody\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:withHeaders\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:withRoutes\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:withSession\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, CodeIgniter\\\\Router\\\\RouteCollection\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$request of method CodeIgniter\\\\CodeIgniter\\:\\:setRequest\\(\\) expects CodeIgniter\\\\HTTP\\\\CLIRequest\\|CodeIgniter\\\\HTTP\\\\IncomingRequest, CodeIgniter\\\\HTTP\\\\Request given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$params of method CodeIgniter\\\\Test\\\\FeatureTestTraitTest\\:\\:populateGlobals\\(\\) expects non\\-empty\\-array\\|null, array\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$session \\(array\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FeatureTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FilterTestTraitTest\\:\\:assertHasFilters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FilterTestTraitTest\\:\\:assertNotFilter\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FilterTestTraitTest\\:\\:assertNotHasFilters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FilterTestTraitTest\\:\\:getFilterCaller\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, array\\<int, string\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\FilterTestTraitTest\\:\\:\\$request \\(CodeIgniter\\\\HTTP\\\\RequestInterface\\) on left side of \\?\\?\\= is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\FilterTestTraitTest\\:\\:\\$response \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\) on left side of \\?\\?\\= is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$result might not be defined\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Test/FilterTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\IniTestTraitTest\\:\\:backupIniValues\\(\\) has parameter \\$keys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/IniTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\IniTestTraitTest\\:\\:\\$iniSettings type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/IniTestTraitTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestLoggerTest\\:\\:provideDidLogMethod\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/TestLoggerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponseTest\\:\\:getTestResponse\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/TestResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponseTest\\:\\:getTestResponse\\(\\) has parameter \\$responseOptions with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/TestResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponseTest\\:\\:provideHttpStatusCodes\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/TestResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$body of method CodeIgniter\\\\HTTP\\\\Response\\:\\:setJSON\\(\\) expects array\\|object\\|string, false given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/TestResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$body of method CodeIgniter\\\\HTTP\\\\Response\\:\\:setJSON\\(\\) expects array\\|object\\|string, true given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Test/TestResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Throttle\\\\ThrottleTest\\:\\:provideTokenTimeCalculationUCs\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Throttle/ThrottleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Throttle\\\\ThrottleTest\\:\\:testTokenTimeCalculationUCs\\(\\) has parameter \\$checkInputs with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Throttle/ThrottleTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\CreditCardRulesTest\\:\\:calculateLuhnChecksum\\(\\) has parameter \\$digits with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/CreditCardRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\CreditCardRulesTest\\:\\:provideValidCCNumber\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/CreditCardRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Validation\\\\Validation constructor expects Config\\\\Validation, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/CreditCardRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\CreditCardRulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/CreditCardRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\DatabaseRelatedRulesTest\\:\\:createRules\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/DatabaseRelatedRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\DatabaseRelatedRulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/DatabaseRelatedRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Validation\\\\Validation constructor expects Config\\\\Validation, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FileRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\FileRulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FileRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertSame\\(\\) with incorrect case\\: assertsame$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:alphaNumericProvider\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideAlpha\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideAlphaDash\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideAlphaNumericPunct\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideAlphaSpace\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideBase64\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideDecimal\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideHex\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideInteger\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideInvalidIntegerType\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideJson\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideNatural\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideNaturalNoZero\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideNumeric\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideString\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideTimeZone\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideValidDate\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideValidEmail\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideValidEmails\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideValidIP\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:provideValidUrl\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Validation\\\\Validation constructor expects Config\\\\Validation, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\FormatRulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideDiffers\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideEquals\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideExactLength\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideFieldExists\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideGreaterThan\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideGreaterThanEqual\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideIfExist\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideInList\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideLessThan\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideLessThanEqual\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideMatches\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideMatchesNestedCases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideMinLengthCases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:providePermitEmpty\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideRequired\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideRequiredWith\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideRequiredWithAndOtherRuleWithValueZero\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideRequiredWithAndOtherRules\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideRequiredWithout\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideRequiredWithoutMultiple\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:provideRequiredWithoutMultipleWithoutFields\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testDiffers\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testDiffersNested\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testEquals\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testFieldExists\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testFieldExists\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testIfExist\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testIfExist\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testMatches\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testMatchesNested\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testPermitEmpty\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testPermitEmpty\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testRequired\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testRequiredWithAndOtherRuleWithValueZero\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testRequiredWithAndOtherRules\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\RulesTest\\:\\:testRequiredWithoutMultipleWithoutFields\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Validation\\\\Validation constructor expects Config\\\\Validation, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\RulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\CreditCardRulesTest\\:\\:calculateLuhnChecksum\\(\\) has parameter \\$digits with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/CreditCardRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\CreditCardRulesTest\\:\\:provideValidCCNumber\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/CreditCardRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Validation\\\\Validation constructor expects Config\\\\Validation, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/CreditCardRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\StrictRules\\\\CreditCardRulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/CreditCardRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\DatabaseRelatedRulesTest\\:\\:createRules\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/DatabaseRelatedRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Validation\\\\Validation constructor expects Config\\\\Validation, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/DatabaseRelatedRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\StrictRules\\\\DatabaseRelatedRulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/DatabaseRelatedRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Validation\\\\Validation constructor expects Config\\\\Validation, stdClass given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/FileRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\StrictRules\\\\FileRulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/FileRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRulesTest\\:\\:provideAlphaSpace\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRulesTest\\:\\:provideInvalidIntegerType\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\StrictRules\\\\FormatRulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/FormatRulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:provideDiffers\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:provideGreaterThanEqualStrict\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:provideGreaterThanStrict\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:provideLessEqualThanStrict\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:provideLessThanStrict\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:provideMatches\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:providePermitEmptyStrict\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:testDiffers\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:testMatches\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:testPermitEmptyStrict\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:testPermitEmptyStrict\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\StrictRules\\\\RulesTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/RulesTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\StrictRules\\\\ValidationTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/StrictRules/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'CONTENT_TYPE\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'application/json\' directly on offset \'CONTENT_TYPE\' of \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:placeholderReplacementResultDetermination\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:provideCanValidatetArrayData\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:provideIfExistRuleWithAsterisk\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:provideIsIntWithInvalidTypeData\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:provideRulesForArrayField\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:provideRulesSetup\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:provideSetRuleRulesFormat\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:provideSplittingOfComplexStringRules\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:provideValidationOfArrayData\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:rule1\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:rule2\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:rule2\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testIfExistRuleWithAsterisk\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testIfExistRuleWithAsterisk\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testRulesForArrayField\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testRulesForArrayField\\(\\) has parameter \\$results with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testRulesForArrayField\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testRulesSetup\\(\\) has parameter \\$errors with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testSplittingOfComplexStringRules\\(\\) has parameter \\$expected with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testValidationOfArrayData\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:testValidationOfArrayData\\(\\) has parameter \\$rules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$config of class CodeIgniter\\\\Validation\\\\Validation constructor expects Config\\\\Validation, stdClass given\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$errors of method CodeIgniter\\\\Validation\\\\Validation\\:\\:check\\(\\) expects array\\<int, string\\>, array\\<string, string\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Validation\\\\ValidationTest\\:\\:\\$config type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/Validation/ValidationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$params of method CodeIgniter\\\\View\\\\Cell\\:\\:prepareParams\\(\\) expects array\\|string\\|null, float given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/CellTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\DecoratorsTest\\:\\:\\$loader \\(CodeIgniter\\\\Autoloader\\\\FileLocator\\) does not accept CodeIgniter\\\\Autoloader\\\\FileLocatorInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/DecoratorsTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\ParserFilterTest\\:\\:\\$loader \\(CodeIgniter\\\\Autoloader\\\\FileLocator\\) does not accept CodeIgniter\\\\Autoloader\\\\FileLocatorInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserFilterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\ParserPluginTest\\:\\:setHints\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserPluginTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\ParserPluginTest\\:\\:setHints\\(\\) has parameter \\$output with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserPluginTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\ParserPluginTest\\:\\:\\$validator \\(CodeIgniter\\\\Validation\\\\Validation\\) does not accept CodeIgniter\\\\Validation\\\\ValidationInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserPluginTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\ParserTest\\:\\:provideEscHandling\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/tests/system/View/ParserTest\\.php\\:339\\:\\:toArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$context of method CodeIgniter\\\\View\\\\Parser\\:\\:setData\\(\\) expects \'attr\'\\|\'css\'\\|\'html\'\\|\'js\'\\|\'raw\'\\|\'url\'\\|null, \'unknown\' given\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/system/View/ParserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\ParserTest\\:\\:\\$loader \\(CodeIgniter\\\\Autoloader\\\\FileLocator\\) does not accept CodeIgniter\\\\Autoloader\\\\FileLocatorInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/View/ParserTest\\.php\\:339\\:\\:\\$bar has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/View/ParserTest\\.php\\:339\\:\\:\\$foo has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property class@anonymous/tests/system/View/ParserTest\\.php\\:369\\:\\:\\$attributes type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ParserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\View\\\\Table\\:\\:compileTemplate\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\View\\\\Table\\:\\:defaultTemplate\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\View\\\\Table\\:\\:prepArgs\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\View\\\\Table\\:\\:setFromArray\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\View\\\\Table\\:\\:setFromDBResult\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\DBResultDummy\\:\\:getFieldNames\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\DBResultDummy\\:\\:getResultArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\TableTest\\:\\:orderedColumnUsecases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\TableTest\\:\\:testAddRowAndGenerateOrderedColumns\\(\\) has parameter \\$heading with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\TableTest\\:\\:testAddRowAndGenerateOrderedColumns\\(\\) has parameter \\$row with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\TableTest\\:\\:testGenerateOrderedColumns\\(\\) has parameter \\$heading with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\TableTest\\:\\:testGenerateOrderedColumns\\(\\) has parameter \\$row with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$array of method CodeIgniter\\\\View\\\\Table\\:\\:makeColumns\\(\\) expects array, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$template of method CodeIgniter\\\\View\\\\Table\\:\\:setTemplate\\(\\) expects array, string given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$columnLimit of method CodeIgniter\\\\View\\\\Table\\:\\:makeColumns\\(\\) expects int, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Table\\:\\:\\$function \\(\\(callable\\(\\)\\: mixed\\)\\|null\\) does not accept \'ticklemyfancy\'\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/TableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\ViewTest\\:\\:\\$loader \\(CodeIgniter\\\\Autoloader\\\\FileLocator\\) does not accept CodeIgniter\\\\Autoloader\\\\FileLocatorInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/system/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$node \\(PhpParser\\\\Node\\\\Stmt\\) of method Utils\\\\PHPStan\\\\CheckUseStatementsAfterLicenseRule\\:\\:processNode\\(\\) should be contravariant with parameter \\$node \\(PhpParser\\\\Node\\) of method PHPStan\\\\Rules\\\\Rule\\<PhpParser\\\\Node\\>\\:\\:processNode\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/utils/PHPStan/CheckUseStatementsAfterLicenseRule.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
