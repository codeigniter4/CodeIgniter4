<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$callback of function spl_autoload_register expects \\(callable\\(string\\)\\: void\\)\\|null, array\\{\\$this\\(CodeIgniter\\\\Autoloader\\\\Autoloader\\), \'loadClass\'\\} given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/Autoloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$callback of function spl_autoload_register expects \\(callable\\(string\\)\\: void\\)\\|null, array\\{\\$this\\(CodeIgniter\\\\Autoloader\\\\Autoloader\\), \'loadClassmap\'\\} given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/Autoloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:chunk\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:chunk\\(\\) has parameter \\$userFunc with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:doOnlyDeleted\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\BaseCommand\\:\\:showError\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/BaseCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\BaseCommand\\:\\:showHelp\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/BaseCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:beep\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:clearScreen\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:error\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:fwrite\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:generateDimensions\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:init\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:newLine\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:parseCommandLine\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:print\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:showProgress\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:table\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:wait\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\CLI\\:\\:write\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Commands\\:\\:discoverCommands\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Commands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CLI\\\\Console\\:\\:showHeader\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/Console.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\CacheInterface\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/CacheInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\BaseHandler\\:\\:deleteMatching\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\BaseHandler\\:\\:remember\\(\\) has parameter \\$callback with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\DummyHandler\\:\\:deleteMatching\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/DummyHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\DummyHandler\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/DummyHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\DummyHandler\\:\\:remember\\(\\) has parameter \\$callback with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/DummyHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:deleteMatching\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\FileHandler\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\MemcachedHandler\\:\\:deleteMatching\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\MemcachedHandler\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\PredisHandler\\:\\:deleteMatching\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\PredisHandler\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\RedisHandler\\:\\:deleteMatching\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\RedisHandler\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\WincacheHandler\\:\\:deleteMatching\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/WincacheHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Cache\\\\Handlers\\\\WincacheHandler\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/WincacheHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:getPost\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\HTTP\\\\Request\\:\\:setLocale\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:bootstrapEnvironment\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:cache\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:callExit\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:detectEnvironment\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:forceSecureAccess\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:gatherOutput\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:getRequestObject\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:getResponseObject\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:initializeKint\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:resolvePlatformExtensions\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:spoofRequestMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:startBenchmark\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\CodeIgniter\\:\\:storePreviousURL\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\CodeIgniter\\:\\:\\$controller type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Database\\\\ShowTableInfo\\:\\:showAllTables\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/ShowTableInfo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Database\\\\ShowTableInfo\\:\\:showDataOfTable\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Database/ShowTableInfo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CellGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\CommandGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ConfigGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ControllerGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\EntityGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\FilterGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\MigrationGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ModelGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ScaffoldGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SeederGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SessionMigrationGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SessionMigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\SessionMigrationGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SessionMigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:generateClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\Generators\\\\ValidationGenerator\\:\\:generateView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\ListCommands\\:\\:listFull\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/ListCommands.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Commands\\\\ListCommands\\:\\:listSimple\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/ListCommands.php',
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
	'message' => '#^Function force_https\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function helper\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Function timer\\(\\) has parameter \\$callable with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Common.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\ComposerScripts\\:\\:postUpdate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/ComposerScripts.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\BaseConfig\\:\\:registerProperties\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\BaseService\\:\\:injectMock\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\BaseService\\:\\:reset\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\BaseService\\:\\:resetSingle\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/BaseService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Config\\:\\:injectMock\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Config\\:\\:reset\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\DotEnv\\:\\:setVariable\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/DotEnv.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:injectMock\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Factories\\:\\:reset\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Factories.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\ForeignCharacters\\:\\:\\$characterList has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/ForeignCharacters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Config\\\\Publisher\\:\\:registerProperties\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/Publisher.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\View\\:\\:\\$filters has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Config\\\\View\\:\\:\\$plugins has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:cachePage\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:forceHTTPS\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:initController\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Controller\\:\\:loadHelpers\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:_whereIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:addUnionStatement\\(\\) has parameter \\$union with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:buildSubquery\\(\\) has parameter \\$builder with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:havingIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:havingNotIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orHavingIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orHavingNotIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orWhereIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:orWhereNotIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:resetRun\\(\\) has no return type specified\\.$#',
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
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:whereIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:whereNotIn\\(\\) has parameter \\$values with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$db \\(CodeIgniter\\\\Database\\\\BaseConnection\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\QueryInterface\\:\\:getOriginalQuery\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:close\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:prepare\\(\\) has parameter \\$func with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BaseConnection\\:\\:transOff\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\BasePreparedQuery\\:\\:execute\\(\\) has parameter \\$data with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BasePreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Config\\:\\:ensureFactory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Config.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:\\$DBDriver\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:\\$connID\\.$#',
	'count' => 2,
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
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeDefault\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeUnique\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:_attributeUnsigned\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Forge\\:\\:reset\\(\\) has no return type specified\\.$#',
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
	'message' => '#^Property CodeIgniter\\\\Database\\\\Migration\\:\\:\\$DBGroup \\(string\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Migration.php',
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
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:force\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\MigrationRunner\\:\\:removeHistory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ModelFactory\\:\\:injectMock\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ModelFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\ModelFactory\\:\\:reset\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/ModelFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\<mysqli, mysqli_result\\>\\:\\:\\$mysqli\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/MySQLi/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:resetSelect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
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
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_attributeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\PreparedQueryInterface\\:\\:execute\\(\\) has parameter \\$data with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/PreparedQueryInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Query\\:\\:compileBinds\\(\\) has no return type specified\\.$#',
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
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$schema\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\:\\:\\$schema\\.$#',
	'count' => 13,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLSRV\\\\Forge\\:\\:_attributeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_attributeAutoIncrement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_attributeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
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
	'message' => '#^Method CodeIgniter\\\\Database\\\\Seeder\\:\\:call\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Seeder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:exceptionHandler\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:render\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Exceptions\\:\\:shutdownHandler\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Iterator\\:\\:add\\(\\) has parameter \\$closure with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Iterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Timer\\:\\:record\\(\\) has parameter \\$callable with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Timer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:prepare\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\:\\:respond\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:getBadgeValue\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/BaseCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:getVarData\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/BaseCollector.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Database\\:\\:collect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Database\\:\\:getConnections\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\History\\:\\:setFiles\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/History.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\Logs\\:\\:collectLogs\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Logs.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Log\\\\Logger\\:\\:\\$logCache \\(array\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Logs.php',
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
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:SMTPEnd\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:appendAttachments\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:batchBCCSend\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:buildHeaders\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:buildMessage\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:setErrorMessage\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:unwrapSpecials\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Email\\\\Email\\:\\:writeHeaders\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property CodeIgniter\\\\Email\\\\Email\\:\\:\\$func_overload \\(bool\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Encryption\\\\Handlers\\\\SodiumHandler\\:\\:parseParams\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Encryption/Handlers/SodiumHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Entity\\\\Entity\\:\\:\\$casts has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Entity\\\\Entity\\:\\:\\$datamap has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Entity\\\\Entity\\:\\:\\$dates has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:on\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:on\\(\\) has parameter \\$callback with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:removeAllListeners\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:removeListener\\(\\) has parameter \\$listener with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:setFiles\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Events\\\\Events\\:\\:simulate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\CastException\\:\\:forInvalidJsonFormatException\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/CastException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\ConfigException\\:\\:forDisabledMigrations\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/ConfigException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\DownloadException\\:\\:forCannotSetBinary\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/DownloadException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\DownloadException\\:\\:forCannotSetCache\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/DownloadException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\DownloadException\\:\\:forCannotSetFilePath\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/DownloadException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\DownloadException\\:\\:forCannotSetStatusCode\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/DownloadException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\DownloadException\\:\\:forNotFoundDownloadSource\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/DownloadException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\FrameworkException\\:\\:forCopyError\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/FrameworkException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\FrameworkException\\:\\:forEnabledZlibOutputCompression\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/FrameworkException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\FrameworkException\\:\\:forFabricatorCreateFailed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/FrameworkException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\FrameworkException\\:\\:forInvalidFile\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/FrameworkException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\FrameworkException\\:\\:forMissingExtension\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/FrameworkException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\FrameworkException\\:\\:forNoHandlers\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/FrameworkException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\ModelException\\:\\:forMethodNotAvailable\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/ModelException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\ModelException\\:\\:forNoDateFormat\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/ModelException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\ModelException\\:\\:forNoPrimaryKey\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/ModelException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\PageNotFoundException\\:\\:forControllerNotFound\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/PageNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\PageNotFoundException\\:\\:forEmptyController\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/PageNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\PageNotFoundException\\:\\:forLocaleNotSupported\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/PageNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\PageNotFoundException\\:\\:forMethodNotFound\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/PageNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\PageNotFoundException\\:\\:forPageNotFound\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/PageNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Exceptions\\\\TestException\\:\\:forInvalidMockClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/TestException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Files\\\\Exceptions\\\\FileException\\:\\:forExpectedDirectory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Files/Exceptions/FileException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Files\\\\Exceptions\\\\FileException\\:\\:forExpectedFile\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Files/Exceptions/FileException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Files\\\\Exceptions\\\\FileException\\:\\:forUnableToMove\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Files/Exceptions/FileException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Files\\\\Exceptions\\\\FileNotFoundException\\:\\:forFileNotFound\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Files/Exceptions/FileNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Files\\\\File\\:\\:\\$size \\(int\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Files/File.php',
];
$ignoreErrors[] = [
	'message' => '#^Expression on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:discoverFilters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:processAliasesToClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:processFilters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:processGlobals\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:processMethods\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Filters\\\\Filters\\:\\:setResponse\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Format\\\\XMLFormatter\\:\\:arrayToXML\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Format/XMLFormatter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CLIRequest\\:\\:parseCommand\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Constructor of class CodeIgniter\\\\HTTP\\\\CURLRequest has an unused parameter \\$config\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:parseOptions\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:resetOptions\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\CURLRequest\\:\\:setResponseHeaders\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addOption\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:addToHeader\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:buildHeaders\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:finalize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\ContentSecurityPolicy\\:\\:generateNonces\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:buildHeaders\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:setBinary\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:setContentTypeByMimeType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:setFilePath\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Files\\\\FileCollection\\:\\:populateFiles\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Expression on left side of \\?\\? is not nullable\\.$#',
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
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Files\\\\UploadedFileInterface\\:\\:move\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/UploadedFileInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:detectLocale\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:detectURI\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Message\\:\\:\\$protocolVersion \\(string\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
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
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Request\\:\\:populateGlobals\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\Response\\:\\:sendCookies\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\URI\\:\\:applyParts\\(\\) has no return type specified\\.$#',
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
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\UserAgent\\:\\:compileData\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/UserAgent.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\HTTP\\\\UserAgent\\:\\:parse\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/UserAgent.php',
];
$ignoreErrors[] = [
	'message' => '#^Function delete_cookie\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/cookie_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function set_cookie\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/cookie_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Right side of && is always true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function d\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/kint_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function d\\(\\) has parameter \\$vars with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/kint_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function dd\\(\\) has no return type specified\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/kint_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function dd\\(\\) has parameter \\$vars with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/kint_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function trace\\(\\) has no return type specified\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/kint_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Function mock\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/test_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Honeypot\\\\Exceptions\\\\HoneypotException\\:\\:forNoHiddenValue\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Honeypot/Exceptions/HoneypotException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Honeypot\\\\Exceptions\\\\HoneypotException\\:\\:forNoNameField\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Honeypot/Exceptions/HoneypotException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Honeypot\\\\Exceptions\\\\HoneypotException\\:\\:forNoTemplate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Honeypot/Exceptions/HoneypotException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Honeypot\\\\Exceptions\\\\HoneypotException\\:\\:isBot\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Honeypot/Exceptions/HoneypotException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Honeypot\\\\Honeypot\\:\\:attachHoneypot\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Honeypot/Honeypot.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Honeypot\\\\Honeypot\\:\\:hasContent\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Honeypot/Honeypot.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\I18n\\\\Time\\:\\:setTestNow\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/Time.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\I18n\\\\Time\\:\\:toDateTimeString\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/Time.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\I18n\\\\TimeLegacy\\:\\:setTestNow\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/TimeLegacy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\I18n\\\\TimeLegacy\\:\\:toDateTimeString\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/I18n/TimeLegacy.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forEXIFUnsupported\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forFileNotSupported\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forImageProcessFailed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forInvalidDirection\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forInvalidImageCreate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forInvalidImageLibraryPath\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forInvalidPath\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forMissingAngle\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forMissingImage\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Exceptions\\\\ImageException\\:\\:forSaveFailed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Exceptions/ImageException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:_text\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:ensureResource\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:reproportion\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$image \\(CodeIgniter\\\\Images\\\\Image\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Comparison operation "\\>\\=" between \\(array\\|float\\|int\\) and 0 results in an error\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:_text\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:ensureResource\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:supportedFormatCheck\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/ImageMagickHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string\\|null of property CodeIgniter\\\\Images\\\\Handlers\\\\ImageMagickHandler\\:\\:\\$resource is not covariant with PHPDoc type resource\\|null of overridden property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$resource\\.$#',
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
	'message' => '#^Method CodeIgniter\\\\Log\\\\Exceptions\\\\LogException\\:\\:forInvalidLogLevel\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Exceptions/LogException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Exceptions\\\\LogException\\:\\:forInvalidMessageType\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Exceptions/LogException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Log\\\\Handlers\\\\ChromeLoggerHandler\\:\\:sendLogs\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/ChromeLoggerHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:chunk\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:chunk\\(\\) has parameter \\$userFunc with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:doOnlyDeleted\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\Exceptions\\\\PagerException\\:\\:forInvalidPaginationGroup\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Exceptions/PagerException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\Exceptions\\\\PagerException\\:\\:forInvalidTemplate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Exceptions/PagerException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\Pager\\:\\:calculateCurrentPage\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\Pager\\:\\:ensureGroup\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Pager\\\\PagerRenderer\\:\\:updatePages\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/PagerRenderer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Publisher\\\\Exceptions\\\\PublisherException\\:\\:forCollision\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Publisher/Exceptions/PublisherException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Publisher\\\\Exceptions\\\\PublisherException\\:\\:forDestinationNotAllowed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Publisher/Exceptions/PublisherException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Publisher\\\\Exceptions\\\\PublisherException\\:\\:forFileNotAllowed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Publisher/Exceptions/PublisherException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Publisher\\\\Publisher\\:\\:verifyAllowed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Publisher/Publisher.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\BaseResource\\:\\:initController\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/BaseResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\RESTful\\\\BaseResource\\:\\:setModel\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/BaseResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\AutoRouter\\:\\:setDirectory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\AutoRouter\\:\\:\\$cliRoutes type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:add\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:cli\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:create\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:create\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:delete\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:discoverRoutes\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:environment\\(\\) has parameter \\$callback with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:get\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:get404Override\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:getControllerName\\(\\) has parameter \\$handler with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:group\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:group\\(\\) has parameter \\$params with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:head\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:match\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:options\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:patch\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:post\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:put\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:resetRoutes\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:set404Override\\(\\) has parameter \\$callable with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\RouteCollection\\:\\:\\$override404 type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:add\\(\\) has parameter \\$to with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:get404Override\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:set404Override\\(\\) has parameter \\$callable with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollectionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:getDefaultNamespace\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Router\\\\RouteCollectionInterface\\:\\:getFilterForRoute\\(\\)\\.$#',
	'count' => 1,
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
	'message' => '#^Expression on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:autoRoute\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:controllerName\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:get404Override\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:handle\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:setDefaultController\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:setDirectory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:setMatchedRoute\\(\\) has parameter \\$handler with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:setRequest\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\Router\\:\\:\\$controller type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouterInterface\\:\\:controllerName\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouterInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouterInterface\\:\\:handle\\(\\) return type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouterInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\Exceptions\\\\SecurityException\\:\\:forDisallowedAction\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/Exceptions/SecurityException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\Exceptions\\\\SecurityException\\:\\:forInvalidControlChars\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/Exceptions/SecurityException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\Exceptions\\\\SecurityException\\:\\:forInvalidSameSite\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/Exceptions/SecurityException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Security\\\\Exceptions\\\\SecurityException\\:\\:forInvalidUTF8Chars\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Security/Exceptions/SecurityException.php',
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
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\Database\\\\PostgreHandler\\:\\:setSelect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/Database/PostgreHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\DatabaseHandler\\:\\:setSelect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/DatabaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\Handlers\\\\FileHandler\\:\\:configureSessionIDRegex\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/FileHandler.php',
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
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:push\\(\\) has no return type specified\\.$#',
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
	'message' => '#^Method CodeIgniter\\\\Session\\\\Session\\:\\:unmarkTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Session\\\\Session\\:\\:\\$sessionExpiration \\(int\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:destroy\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:keepFlashdata\\(\\) has no return type specified\\.$#',
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
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:setFlashdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:setTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:unmarkFlashdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Session\\\\SessionInterface\\:\\:unmarkTempdata\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/SessionInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:clearInsertCache\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:dontSeeInDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:loadDependencies\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:migrateDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:regressDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:resetMigrationSeedCount\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:runSeeds\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:seeInDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:seeNumRecords\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:seed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:setUpDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:setUpMigrate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:setUpSeed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\CIDatabaseTestCase\\:\\:tearDownDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
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
	'message' => '#^Access to an undefined property object\\:\\:\\$createdField\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property object\\:\\:\\$deletedField\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property object\\:\\:\\$updatedField\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:resetCounts\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:clearInsertCache\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:dontSeeInDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:loadDependencies\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:migrateDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:regressDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:resetMigrationSeedCount\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:runSeeds\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:seeInDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:seeNumRecords\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:seed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:setUpDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:setUpMigrate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:setUpSeed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\FeatureTestCase\\:\\:tearDownDatabase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$bodyFormat \\(string\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$clean \\(bool\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\CIUnitTestCase\\:\\:\\$session \\(array\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
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
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCache\\:\\:assertHas\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCache\\:\\:assertHasValue\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCache\\:\\:assertMissing\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCache\\:\\:initialize\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCache\\:\\:remember\\(\\) has parameter \\$callback with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockCodeIgniter\\:\\:callExit\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockCodeIgniter.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$insert_id on object\\|resource\\|false\\.$#',
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
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:\\$returnValues has no type specified\\.$#',
	'count' => 1,
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
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockFileLogger\\:\\:\\$destination has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockFileLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockLanguage\\:\\:disableIntlSupport\\(\\) has no return type specified\\.$#',
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
	'message' => '#^Property CodeIgniter\\\\Test\\\\Mock\\\\MockSecurityConfig\\:\\:\\$excludeURIs has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockSecurityConfig.php',
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
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestLogger\\:\\:cleanup\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\TestLogger\\:\\:\\$op_logs has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertCookie\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertCookieExpired\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertCookieMissing\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertDontSee\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertDontSeeElement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertHeader\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertHeaderMissing\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertJSONExact\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertJSONFragment\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertNotOK\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertNotRedirect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertOK\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertRedirect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertRedirectTo\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertSee\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertSeeElement\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertSeeInField\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertSeeLink\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertSessionHas\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertSessionMissing\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:assertStatus\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Throttle\\\\Throttler\\:\\:\\$testTime \\(int\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Throttle/Throttler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Exceptions\\\\ValidationException\\:\\:forGroupNotArray\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Exceptions/ValidationException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Exceptions\\\\ValidationException\\:\\:forGroupNotFound\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Exceptions/ValidationException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Exceptions\\\\ValidationException\\:\\:forInvalidTemplate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Exceptions/ValidationException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Exceptions\\\\ValidationException\\:\\:forNoRuleSets\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Exceptions/ValidationException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Exceptions\\\\ValidationException\\:\\:forRuleNotFound\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Exceptions/ValidationException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:isClosure\\(\\) has parameter \\$rule with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:loadRuleSets\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:setRuleGroup\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\ValidationInterface\\:\\:setRuleGroup\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/ValidationInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method CodeIgniter\\\\Config\\\\Factories\\:\\:cells\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cell\\:\\:getMethodParams\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Cell\\:\\:\\$cache \\(CodeIgniter\\\\Cache\\\\CacheInterface\\) in empty\\(\\) is not falsy\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Cells\\\\Cell\\:\\:setView\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/system/Traits/PropertiesTrait\\.php\\:47\\:\\:getProperties\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method class@anonymous/system/Traits/PropertiesTrait\\.php\\:47\\:\\:getProperties\\(\\) has parameter \\$obj with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cells/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Exceptions\\\\ViewException\\:\\:forInvalidCellClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Exceptions/ViewException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Exceptions\\\\ViewException\\:\\:forInvalidCellMethod\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Exceptions/ViewException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Exceptions\\\\ViewException\\:\\:forInvalidCellParameter\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Exceptions/ViewException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Exceptions\\\\ViewException\\:\\:forInvalidDecorator\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Exceptions/ViewException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Exceptions\\\\ViewException\\:\\:forMissingCellParameters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Exceptions/ViewException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Exceptions\\\\ViewException\\:\\:forNoCellClass\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Exceptions/ViewException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Exceptions\\\\ViewException\\:\\:forTagSyntaxError\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Exceptions/ViewException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:addPlugin\\(\\) has parameter \\$callback with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Config\\\\View\\:\\:\\$plugins \\(array\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:_compileTemplate\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:_setFromArray\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Table\\:\\:_setFromDBResult\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Table\\:\\:\\$function type has no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:endSection\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:extend\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:logPerformance\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:renderSection\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\View\\:\\:section\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\View\\:\\:\\$tempData has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/View.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
