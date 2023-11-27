<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\HTTP\\\\CLIRequest\\|CodeIgniter\\\\HTTP\\\\IncomingRequest of property App\\\\Controllers\\\\BaseController\\:\\:\\$request is not the same as PHPDoc type CodeIgniter\\\\HTTP\\\\RequestInterface of overridden property CodeIgniter\\\\Controller\\:\\:\\$request\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/BaseController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function method_exists\\(\\) with \'Composer\\\\\\\\InstalledVersions\' and \'getAllRawData\' will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/Autoloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Config\\\\Autoload\\:\\:\\$helpers \\(array\\<int, string\\>\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Autoloader/Autoloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 13,
	'path' => __DIR__ . '/system/Autoloader/FileLocator.php',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 16,
	'path' => __DIR__ . '/system/BaseModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\BaseModel\\:\\:chunk\\(\\) has parameter \\$userFunc with no signature specified for Closure\\.$#',
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
	'message' => '#^Only booleans are allowed in &&, array\\|null given on the left side\\.$#',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/CLI/BaseCommand.php',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array given on the right side\\.$#',
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
	'message' => '#^Only booleans are allowed in a negated boolean, array given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string given\\.$#',
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
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/CLI/CLI.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Cache/CacheFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/BaseHandler.php',
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
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Cache/Handlers/PredisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 6,
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
	'message' => '#^Only booleans are allowed in a negated boolean, array\\<int\\<0, max\\>, array\\<int, mixed\\>\\> given\\.$#',
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
	'message' => '#^Accessing offset \'encryption\\.key\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Encryption/GenerateKey.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/CellGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/CommandGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ConfigGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an elseif condition, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in \\|\\|, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in \\|\\|, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ControllerGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/EntityGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/FilterGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/MigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ModelGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an elseif condition, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, mixed given\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ScaffoldGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/SeederGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/SessionMigrationGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SessionMigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SessionMigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/SessionMigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/SessionMigrationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
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
	'message' => '#^Only booleans are allowed in &&, mixed given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, mixed given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Generators/ValidationGenerator.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
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
	'message' => '#^Only booleans are allowed in &&, int given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Server/Serve.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'CI_ENVIRONMENT\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Commands/Utilities/Environment.php',
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
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/AutoRouterImproved/ControllerMethodReader.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Commands/Utilities/Routes/ControllerMethodReader.php',
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
	'count' => 8,
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Config/BaseService.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array given\\.$#',
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
	'message' => '#^Only booleans are allowed in a negated boolean, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Config/DotEnv.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string given on the left side\\.$#',
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
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Config/Services.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Controller.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array given\\.$#',
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
	'message' => '#^Call to function is_string\\(\\) with non\\-falsy\\-string will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 40,
	'path' => __DIR__ . '/system/Database/BaseBuilder.php',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 13,
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
	'message' => '#^Method CodeIgniter\\\\Database\\\\BasePreparedQuery\\:\\:execute\\(\\) has parameter \\$data with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BasePreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 10,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, array\\<array\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, array\\<object\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$className \\(class\\-string\\) of method CodeIgniter\\\\Database\\\\BaseResult\\:\\:getCustomResultObject\\(\\) should be contravariant with parameter \\$className \\(string\\) of method CodeIgniter\\\\Database\\\\ResultInterface\\<TConnection,TResult\\>\\:\\:getCustomResultObject\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/BaseResult.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Database/BaseUtils.php',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
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
	'message' => '#^Only booleans are allowed in &&, string given on the left side\\.$#',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 8,
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
	'message' => '#^Only booleans are allowed in &&, int\\<0, max\\> given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MigrationRunner.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, max\\> given\\.$#',
	'count' => 4,
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 12,
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
	'count' => 6,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\MySQLi\\\\Forge\\:\\:\\$createDatabaseStr is not the same as PHPDoc type string\\|false of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$createDatabaseStr\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/MySQLi/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property CodeIgniter\\\\Database\\\\BaseConnection\\<mysqli, mysqli_result\\>\\:\\:\\$mysqli\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Database/MySQLi/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Database\\\\MySQLi\\\\PreparedQuery\\) of method CodeIgniter\\\\Database\\\\MySQLi\\\\PreparedQuery\\:\\:_prepare\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Database\\\\BasePreparedQuery\\<TConnection, TStatement, TResult\\>\\)\\) of method CodeIgniter\\\\Database\\\\BasePreparedQuery\\<mysqli,mysqli_stmt,mysqli_result\\>\\:\\:_prepare\\(\\)$#',
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
	'count' => 4,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:resetSelect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\OCI8\\\\Connection of property CodeIgniter\\\\Database\\\\OCI8\\\\Builder\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:\\$db\\.$#',
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
	'message' => '#^Only booleans are allowed in a negated boolean, array given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array\\<stdClass\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Connection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\OCI8\\\\Connection\\:\\:\\$escapeChar is not the same as PHPDoc type array\\|string of overridden property CodeIgniter\\\\Database\\\\BaseConnection\\<resource,resource\\>\\:\\:\\$escapeChar\\.$#',
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
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\OCI8\\\\Connection of property CodeIgniter\\\\Database\\\\OCI8\\\\PreparedQuery\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection\\<resource, resource\\> of overridden property CodeIgniter\\\\Database\\\\BasePreparedQuery\\<resource,resource,resource\\>\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Database\\\\OCI8\\\\PreparedQuery\\) of method CodeIgniter\\\\Database\\\\OCI8\\\\PreparedQuery\\:\\:_prepare\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Database\\\\BasePreparedQuery\\<TConnection, TStatement, TResult\\>\\)\\) of method CodeIgniter\\\\Database\\\\BasePreparedQuery\\<resource,resource,resource\\>\\:\\:_prepare\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\OCI8\\\\Utils\\:\\:\\$listDatabases is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$listDatabases\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/OCI8/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/system/Database/Postgre/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array\\<int\\|string, array\\<int, int\\|string\\>\\|string\\> given\\.$#',
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
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\Postgre\\\\Connection of property CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\|bool\\|string\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\Forge\\:\\:_alterTable\\(\\) should be covariant with return type \\(array\\<string\\>\\|string\\|false\\) of method CodeIgniter\\\\Database\\\\Forge\\:\\:_alterTable\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Database\\\\Postgre\\\\PreparedQuery\\) of method CodeIgniter\\\\Database\\\\Postgre\\\\PreparedQuery\\:\\:_prepare\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Database\\\\BasePreparedQuery\\<TConnection, TStatement, TResult\\>\\)\\) of method CodeIgniter\\\\Database\\\\BasePreparedQuery\\<PgSql\\\\Connection,PgSql\\\\Result,PgSql\\\\Result\\>\\:\\:_prepare\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Postgre/Result.php',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 15,
	'path' => __DIR__ . '/system/Database/SQLSRV/Builder.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$selectOverride \\(bool\\) of method CodeIgniter\\\\Database\\\\SQLSRV\\\\Builder\\:\\:compileSelect\\(\\) should be contravariant with parameter \\$selectOverride \\(mixed\\) of method CodeIgniter\\\\Database\\\\BaseBuilder\\:\\:compileSelect\\(\\)$#',
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
	'count' => 8,
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
	'count' => 6,
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
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\SQLSRV\\\\Connection of property CodeIgniter\\\\Database\\\\SQLSRV\\\\PreparedQuery\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection\\<resource, resource\\> of overridden property CodeIgniter\\\\Database\\\\BasePreparedQuery\\<resource,resource,resource\\>\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Database\\\\SQLSRV\\\\PreparedQuery\\) of method CodeIgniter\\\\Database\\\\SQLSRV\\\\PreparedQuery\\:\\:_prepare\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Database\\\\BasePreparedQuery\\<TConnection, TStatement, TResult\\>\\)\\) of method CodeIgniter\\\\Database\\\\BasePreparedQuery\\<resource,resource,resource\\>\\:\\:_prepare\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLSRV/Result.php',
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
	'message' => '#^PHPDoc type CodeIgniter\\\\Database\\\\SQLite3\\\\Connection of property CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:\\$db is not the same as PHPDoc type CodeIgniter\\\\Database\\\\BaseConnection of overridden property CodeIgniter\\\\Database\\\\Forge\\:\\:\\$db\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(array\\|string\\|null\\) of method CodeIgniter\\\\Database\\\\SQLite3\\\\Forge\\:\\:_alterTable\\(\\) should be covariant with return type \\(array\\<string\\>\\|string\\|false\\) of method CodeIgniter\\\\Database\\\\Forge\\:\\:_alterTable\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Forge.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\Database\\\\SQLite3\\\\PreparedQuery\\) of method CodeIgniter\\\\Database\\\\SQLite3\\\\PreparedQuery\\:\\:_prepare\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\Database\\\\BasePreparedQuery\\<TConnection, TStatement, TResult\\>\\)\\) of method CodeIgniter\\\\Database\\\\BasePreparedQuery\\<SQLite3,SQLite3Stmt,SQLite3Result\\>\\:\\:_prepare\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(SQLite3Result\\|false\\) of method CodeIgniter\\\\Database\\\\SQLite3\\\\PreparedQuery\\:\\:_getResult\\(\\) should be covariant with return type \\(object\\|resource\\|null\\) of method CodeIgniter\\\\Database\\\\BasePreparedQuery\\<SQLite3,SQLite3Stmt,SQLite3Result\\>\\:\\:_getResult\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/PreparedQuery.php',
];
$ignoreErrors[] = [
	'message' => '#^Class stdClass referenced with incorrect case\\: stdclass\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Database/SQLite3/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
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
	'message' => '#^PHPDoc type string of property CodeIgniter\\\\Database\\\\SQLite3\\\\Utils\\:\\:\\$optimizeTable is not the same as PHPDoc type bool\\|string of overridden property CodeIgniter\\\\Database\\\\BaseUtils\\:\\:\\$optimizeTable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/SQLite3/Utils.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Seeder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Seeder\\:\\:call\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Seeder.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Database\\\\Seeder\\:\\:run\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Database/Seeder.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Debug/BaseExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Debug/ExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Debug/Exceptions.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Iterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Debug/Timer.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Debug/Toolbar.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Debug/Toolbar/Collectors/History.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
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
	'count' => 14,
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
	'message' => '#^Static property CodeIgniter\\\\Email\\\\Email\\:\\:\\$func_overload \\(bool\\) in isset\\(\\) is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Email/Email.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Encryption/Encryption.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Encryption/Handlers/OpenSSLHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, array\\|string\\|null given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Encryption/Handlers/OpenSSLHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Encryption/Handlers/SodiumHandler.php',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Entity/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument \\#1 \\$name \\(\'Config\\\\\\\\Modules\'\\) passed to function config does not extend CodeIgniter\\\\\\\\Config\\\\\\\\BaseConfig\\.$#',
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
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Events/Events.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type int of property CodeIgniter\\\\Exceptions\\\\PageNotFoundException\\:\\:\\$code is not the same as PHPDoc type mixed of overridden property Exception\\:\\:\\$code\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Exceptions/PageNotFoundException.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method SplFileInfo\\:\\:getBasename\\(\\) with incorrect case\\: getBaseName$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Files/File.php',
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
	'count' => 2,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Expression on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Filters/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/CLIRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 10,
	'path' => __DIR__ . '/system/HTTP/CURLRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Constructor of class CodeIgniter\\\\HTTP\\\\CURLRequest has an unused parameter \\$config\\.$#',
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
	'count' => 9,
	'path' => __DIR__ . '/system/HTTP/ContentSecurityPolicy.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_USER_AGENT\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\DownloadResponse\\) of method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:noCache\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\HTTP\\\\Response\\)\\) of method CodeIgniter\\\\HTTP\\\\Response\\:\\:noCache\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/DownloadResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\DownloadResponse\\) of method CodeIgniter\\\\HTTP\\\\DownloadResponse\\:\\:noCache\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\)\\) of method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:noCache\\(\\)$#',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Files/FileCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array given on the right side\\.$#',
	'count' => 2,
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
	'count' => 5,
	'path' => __DIR__ . '/system/HTTP/IncomingRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type CodeIgniter\\\\HTTP\\\\URI of property CodeIgniter\\\\HTTP\\\\IncomingRequest\\:\\:\\$uri is not the same as PHPDoc type CodeIgniter\\\\HTTP\\\\URI\\|null of overridden property CodeIgniter\\\\HTTP\\\\OutgoingRequest\\:\\:\\$uri\\.$#',
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
	'message' => '#^Property CodeIgniter\\\\HTTP\\\\Message\\:\\:\\$protocolVersion \\(string\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Message.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/HTTP/Negotiate.php',
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
	'message' => '#^Only booleans are allowed in an if condition, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/Request.php',
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
	'count' => 6,
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
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/HTTP/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\!\\=\\= between mixed and null will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HTTP/SiteURI.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 15,
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
	'count' => 3,
	'path' => __DIR__ . '/system/HTTP/UserAgent.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array\\<string, string\\> given on the right side\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/HTTP/UserAgent.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Helpers/array_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/date_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 1024\\> given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 128\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 16\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 1\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 2048\\> given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 256\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 2\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 32\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 4\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 512\\> given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 64\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/filesystem_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, 8\\> given\\.$#',
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
	'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, int\\<0, max\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/form_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/html_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/number_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/test_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Helpers/text_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/text_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Implicit array creation is not allowed \\- variable \\$atts might not exist\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Helpers/url_helper.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string given\\.$#',
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
	'count' => 2,
	'path' => __DIR__ . '/system/Honeypot/Honeypot.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in \\|\\|, int given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/HotReloader/HotReloader.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
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
	'message' => '#^Method CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:__call\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Images/Handlers/BaseHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Images\\\\Handlers\\\\BaseHandler\\:\\:\\$image \\(CodeIgniter\\\\Images\\\\Image\\) in empty\\(\\) is not falsy\\.$#',
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
	'count' => 10,
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Log/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Strict comparison using \\=\\=\\= between true and true will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Handlers/FileHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Logger.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$level \\(string\\) of method CodeIgniter\\\\Log\\\\Logger\\:\\:log\\(\\) should be contravariant with parameter \\$level \\(mixed\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:log\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Log/Logger.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 21,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Model\\:\\:chunk\\(\\) has parameter \\$userFunc with no signature specified for Closure\\.$#',
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
	'message' => '#^Return type \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Model\\:\\:__call\\(\\) should be covariant with return type \\(\\$this\\(CodeIgniter\\\\BaseModel\\)\\|null\\) of method CodeIgniter\\\\BaseModel\\:\\:__call\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset mixed directly on \\$_GET is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, array given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Pager.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method CodeIgniter\\\\Pager\\\\PagerRenderer\\:\\:getNext\\(\\) with incorrect case\\: getnext$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Pager/Views/default_simple.php',
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
	'message' => '#^PHPDoc type CodeIgniter\\\\HTTP\\\\CLIRequest\\|CodeIgniter\\\\HTTP\\\\IncomingRequest of property CodeIgniter\\\\RESTful\\\\BaseResource\\:\\:\\$request is not the same as PHPDoc type CodeIgniter\\\\HTTP\\\\RequestInterface of overridden property CodeIgniter\\\\Controller\\:\\:\\$request\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/RESTful/BaseResource.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/RESTful/ResourceController.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Router\\\\AutoRouter\\:\\:\\$cliRoutes type has no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, Config\\\\Routing given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/AutoRouterImproved.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type int of property CodeIgniter\\\\Router\\\\Exceptions\\\\RedirectException\\:\\:\\$code is not the same as PHPDoc type mixed of overridden property Exception\\:\\:\\$code\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/Exceptions/RedirectException.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
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
	'message' => '#^Method CodeIgniter\\\\Router\\\\RouteCollection\\:\\:set404Override\\(\\) has parameter \\$callable with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, array\\<int\\|string, array\\|\\(callable\\)\\> given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Router/RouteCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Router/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Expression on left side of \\?\\? is not nullable\\.$#',
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
	'message' => '#^Method CodeIgniter\\\\Router\\\\Router\\:\\:setMatchedRoute\\(\\) has parameter \\$handler with no signature specified for callable\\.$#',
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
	'message' => '#^Only booleans are allowed in a negated boolean, string given\\.$#',
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
	'count' => 3,
	'path' => __DIR__ . '/system/Session/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/MemcachedHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Session/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, string given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Session/Handlers/RedisHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Accessing offset \'HTTP_X_REQUESTED_WITH\' directly on \\$_SERVER is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 13,
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Test/CIDatabaseTestCase.php',
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
	'message' => '#^Only booleans are allowed in a negated boolean, CodeIgniter\\\\CodeIgniter given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/CIUnitTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function is_array\\(\\) with non\\-empty\\-array will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/DOMParser.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 7,
	'path' => __DIR__ . '/system/Test/DOMParser.php',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Fabricator\\:\\:resetCounts\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function model expects a valid class string, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Fabricator.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning \'test\' directly on offset \'HTTPS\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Assigning string directly on offset \'REQUEST_METHOD\' of \\$_SERVER is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 10,
	'path' => __DIR__ . '/system/Test/FeatureTestCase.php',
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
	'message' => '#^Only booleans are allowed in a negated boolean, CodeIgniter\\\\Router\\\\RouteCollection\\|null given\\.$#',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
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
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockCache\\:\\:get\\(\\) should be covariant with return type \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Cache\\\\CacheInterface\\:\\:get\\(\\)$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Test/Mock/MockCache.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockCache\\:\\:remember\\(\\) should be covariant with return type \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Cache\\\\Handlers\\\\BaseHandler\\:\\:remember\\(\\)$#',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Test/Mock/MockConnection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\Mock\\\\MockConnection\\:\\:_close\\(\\) should return mixed but return statement is missing\\.$#',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
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
	'message' => '#^Return type \\(mixed\\) of method CodeIgniter\\\\Test\\\\Mock\\\\MockResult\\:\\:fetchAssoc\\(\\) should be covariant with return type \\(array\\|false\\|null\\) of method CodeIgniter\\\\Database\\\\BaseResult\\<object\\|resource,object\\|resource\\>\\:\\:fetchAssoc\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/Mock/MockResult.php',
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
	'message' => '#^Parameter \\#1 \\$level \\(string\\) of method CodeIgniter\\\\Test\\\\TestLogger\\:\\:log\\(\\) should be contravariant with parameter \\$level \\(mixed\\) of method Psr\\\\Log\\\\LoggerInterface\\:\\:log\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\Test\\\\TestLogger\\:\\:\\$op_logs has no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Test\\\\TestResponse\\:\\:__call\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Test/TestResponse.php',
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
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/CreditCardRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/DotArrayFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a negated boolean, array\\|null given\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/system/Validation/FileRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Validation/FormatRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 11,
	'path' => __DIR__ . '/system/Validation/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/system/Validation/StrictRules/Rules.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Validation\\\\Validation\\:\\:isClosure\\(\\) has parameter \\$rule with no signature specified for Closure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/Validation/Validation.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/Validation/Views/list.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined static method CodeIgniter\\\\Config\\\\Factories\\:\\:cells\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Cell.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Filters.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 9,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\View\\\\Parser\\:\\:addPlugin\\(\\) has parameter \\$callback with no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Parser.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Plugins.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 9,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, float given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Property CodeIgniter\\\\View\\\\Table\\:\\:\\$function type has no signature specified for callable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/system/View/Table.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Short ternary operator is not allowed\\. Use null coalesce operator if applicable or consider using long ternary\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/system/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$node \\(PhpParser\\\\Node\\\\Stmt\\) of method Utils\\\\PHPStan\\\\CheckUseStatementsAfterLicenseRule\\:\\:processNode\\(\\) should be contravariant with parameter \\$node \\(PhpParser\\\\Node\\) of method PHPStan\\\\Rules\\\\Rule\\<PhpParser\\\\Node\\>\\:\\:processNode\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/utils/PHPStan/CheckUseStatementsAfterLicenseRule.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
