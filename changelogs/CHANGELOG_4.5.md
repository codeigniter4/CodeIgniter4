# Changelog 4.5

## [v4.5.8](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.8) (2025-01-19)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.7...v4.5.8)

### Security

* **HTTP** *Validation of header name and value*: Fixed a potential vulnerability on lack of proper header validation
    for its name and value. See the [security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-x5mq-jjr3-vmx6)
    for more information. Credits to @neznaika0 for reporting.
* **Security** fix: ensure csrf token is string by @datlechin in https://github.com/codeigniter4/CodeIgniter4/pull/9365

### Fixed Bugs

* fix: gather affected rows after query call failed by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9363

### Refactoring

* refactor: use more strict result check on preg_match_all() result by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9361
* refactor: Fix phpstan if.condNotBoolean by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9368
* refactor: Fix phpstan when delete string key by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9369
* refactor: Fix phpstan greaterOrEqual.invalid by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9370
* refactor: Fix phpstan nullCoalesce by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9374
* refactor: Fix phpstan isset offset by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9383
* refactor: Fix phpstan return.missing by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9382
* refactor: Fix phpstan booleanAnd.rightAlwaysTrue by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9367
* refactor: Fix phpstan codeigniter.configArgumentInstanceof by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9390
* refactor: Use `strtolower` with `str_contains`/`str_**_with` as replacement for `stripos` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9414

## [v4.5.7](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.7) (2024-12-31)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.6...v4.5.7)

### Fixed Bugs

* fix: handle namespaced helper found on Common helper by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9354
* fix: `Forge::dropColumn()` always returns `false` on SQLite3 driver by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9351

### Refactoring

* refactor: enable AddArrowFunctionReturnTypeRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9343

## [v4.5.6](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.6) (2024-12-28)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.5...v4.5.6)

### Fixed Bugs

* fix: auto_link() converts invalid strings like `://codeigniter.com` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9180
* fix: change session start log level by @element-code in https://github.com/codeigniter4/CodeIgniter4/pull/9221
* fix: `getValidated()` when validation multiple asterisk by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9220
* fix: Parser - Equal key name replace conflict by @CosDiabos in https://github.com/codeigniter4/CodeIgniter4/pull/9246
* fix: case-insensitivity in the `like()` method when in use with accented characters by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9238
* fix: TypeError for routes when translateURIDashes is enabled by @maniaba in https://github.com/codeigniter4/CodeIgniter4/pull/9209
* fix: `fetchGlobal()` with numeric key by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9251
* fix: curl request crashes with params that give an int once hexed. by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/9198
* docs: allow boolean values in the model for PHPStan by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/9276
* fix: respect complex language strings when using validation by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9201
* fix: `DownloadResponse` cache headers by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9237
* docs: fix `@param` `ResponseInterface::setJSON()` also accepts objects by @JulianAtkins in https://github.com/codeigniter4/CodeIgniter4/pull/9287
* fix: [CURLRequest] body contains "HTTP/1.0 200 Connection established" by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/9285
* fix: `Postgre\Connection::reconnect()` `TypeError` in `pg_ping()` by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/9279
* fix: primary key mapping in the model for the entity by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9307
* fix: check if defined `WRITEPATH` exists by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9317
* fix: handling binary data for prepared statement by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9337

### Refactoring

* refactor: enable TypedPropertyFromAssignsRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9184
* refactor: enable ClosureReturnTypeRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9187
* refactor: remove unnecessary `is_countable()` check in `getMethodParams()` by @datamweb in https://github.com/codeigniter4/CodeIgniter4/pull/9206
* refactor: add more readonly property definitions on AutoRouteCollector and SiteURI by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9210
* refactor: starter key handling in SodiumHandler by @datamweb in https://github.com/codeigniter4/CodeIgniter4/pull/9207
* refactor: enable rector code quality level 14 by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9232
* refactor: cleanup `DatabaseHandler::gc()` for session by @grimpirate in https://github.com/codeigniter4/CodeIgniter4/pull/9230
* refactor: enable rector code quality level 15 by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9243
* refactor: enable SimplifyBoolIdenticalTrueRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9244
* refactor: enable FlipTypeControlToUseExclusiveTypeRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9253
* refactor: flip assert and actual value position on tests by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9260
* perf: Improve call as `service()` by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9248
* refactor: use compare empty array on Forge on keys property by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9267
* refactor: Fix `phpstan` errors related to `Autoloader` by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9249
* refactor: use `Superglobals` in setting 'REQUEST_METHOD' in `FeatureT… by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9294
* refactor: use `baseURI` instead of `base_uri` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9296
* refactor: Apply code quality level 31 for rector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9303
* refactor: rename `stdclass` to `stdClass` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9312
* refactor: fix `phpDoc.parseError` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9313
* refactor: fix `method.nameCase` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9315
* refactor: rename `controller` to `Controller` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9314
* refactor: fix implicit array creation by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9316
* refactor: follow up implicit variable array by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9319
* refactor: split phpstan-baseline into smaller files by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9299
* refactor: upgrade to use phpstan 2 and rector 2 by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9322
* refactor: fix `Forge::processIndexes()` for empty `$this->fields` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9330
* refactor: `Reflection*::setAccessible()` is now no-op in PHP 8.1 by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9331
* refactor: add `@throws RedirectException` in `Controller::initController` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9327
* refactor: fix warning on new static usage by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9342
* refactor: fix used void return type by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9341
* refactor: enable instanceof and strictBooleans rector set by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9339

## [v4.5.5](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.5) (2024-09-07)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.4...v4.5.5)

### Fixed Bugs

* fix: Validation rule `differs`/`matches` with dot array by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9103
* fix: update preload.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9111
* fix: [Validation] TypeError when using numeric field names by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9142
* fix: `auto_link()` regexp by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9169

### Refactoring

* refactor: reduce_multiples() and fix user guide by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9099
* refactor: enable AddMethodCallBasedStrictParamTypeRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9156
* refactor: BaseBuilder by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9157
* refactor: improve error message for missing PHP DB extensions by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9160
* refactor: fix typo in BaseConnection.php by @ThomasMeschke in https://github.com/codeigniter4/CodeIgniter4/pull/9170

## [v4.5.4](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.4) (2024-07-27)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.3...v4.5.4)

### Fixed Bugs

* fix: [OCI8] Easy Connect string validation by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9006
* fix: [QueryBuilder] select() with RawSql may cause TypeError by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9009
* fix: [QueryBuilder] `select()` does not escape after `NULL` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9010
* fix: allow string as parameter to CURLRequest version by @tangix in https://github.com/codeigniter4/CodeIgniter4/pull/9021
* fix: `spark phpini:check` may cause TypeError by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9026
* fix: Prevent invalid session handlers by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9036
* fix: DebugBar CSS for daisyUI by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9046
* docs: `referrer` is undefined by @totoprayogo1916 in https://github.com/codeigniter4/CodeIgniter4/pull/9059
* fix: filters passed to the ``$routes->group()`` are not merged into the filters passed to the inner routes by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9064

### Refactoring

* refactor: use first class callable on function call by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9004
* refactor: enable AddClosureVoidReturnTypeWhereNoReturnRector to add void return on closure by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9008
* refactor: enable AddFunctionVoidReturnTypeWhereNoReturnRector to add void to functions by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9014
* refactor: Enable phpunit 10 attribute Rector rules by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/9015
* refactor: fix `Throttler::check()` $tokens by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9067

## [v4.5.3](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.3) (2024-06-25)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.2...v4.5.3)

### Fixed Bugs

* fix: `RedisHandler::deleteMatching()` not deleting matching keys if cache prefix is used by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8952
* fix: TypeError in DefinedRouteCollector::collect() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8957
* fix: `migrate:rollback -b` does not work due to TypeError by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8958
* fix: [Validation] `if_exist` does not work with array data by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8959
* chore: add `Config` namespace to appstarter autoload.psr4 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8963
* fix: `spark routes` may show BadRequestException when a route has a regexp by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8974
* docs: fix incorrect description for route group filter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8978
* fix: return and param types of BaseConnection by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8983
* fix: precedence of command classes with the same `$name` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8898
* fix: [OCI8] if conditions to build DSN by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8986
* fix: [Auto Routing Improved] Default Method Fallback does not work with `$translateUriToCamelCase` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8980
* fix: `command()` may execute `rewrite.php` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8995

### Refactoring

* refactor: BaseBuilder::orderBy() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8970
* refactor: using phpunit 10 assertObjectHasNotProperty() and assertObjectHasProperty() by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/8991

## [v4.5.2](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.2) (2024-06-10)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.1...v4.5.2)

### Fixed Bugs

* chore: fix phpunit.xml.dist for appstarter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8785
* fix: update `preload.php` for 4.5 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8805
* fix: [ErrorException] Undefined array key in `spark phpini:check` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8804
* fix: incorrect Security exception message by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8818
* fix: [QueryBuilder] TypeError in join() with BETWEEN by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8792
* fix: [SQLSRV] Query Builder always sets `"<database>"."<schema>".` to the table name. by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/8786
* fix: remove unused undefined param $raw in MockCache::save() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8847
* fix: FileCollection pseudo-regex by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8868
* fix: [Model] casting may throw InvalidArgumentException: Invalid parameter: nullable by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8870
* fix: [Model] casting causes TypeError when finding no record by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8871
* fix: correct property default values in Email by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8855
* fix: CLI::promptByMultipleKeys() and prompt() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8873
* fix: [Postgres] show missing error message by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8913
* fix: TypeError in  number_to_amount()  by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8932
* fix: Model::find() returns incorrect data with casting by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8933

### Refactoring

* refactor: remove unused path parameter on PhpStreamWrapper::stream_open() by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/8926

## [v4.5.1](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.1) (2024-04-14)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.0...v4.5.1)

### Fixed Bugs

* fix: TypeError in form() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8736
* fix: [DebugBar] TypeError in Toolbar by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8727
* fix: TypeError when Time is passed to Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8738
* docs: added Config\Feature::$oldFilterOrder to app/Config/Feature.php… by @mullernato in https://github.com/codeigniter4/CodeIgniter4/pull/8749
* fix: Factories::get() cannot get defined classes by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8744
* fix: `BaseConnection::escape()` does not accept Stringable by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8756
* fix: [CURLRequest] `getHeaderLine('Content-Type')` causes InvalidArgumentException by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8760
* fix: [CURLRequest] construct param $config is not used by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8761
* fix: [FileLocator] Cannot declare class XXX, because the name is already in use by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8745
* fix: [DebugBar] Toolbar display may be broken by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8772
* fix: Cannot declare class CodeIgniter\Config\Services, because the name is already in use by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8776
* docs: fix Postgre DSN sample by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8774

### Refactoring

* test: refactor Config/Registrar.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8731
* test: add return void by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8746
* refactor: system/CLI/BaseCommand.php by @mcsaygili in https://github.com/codeigniter4/CodeIgniter4/pull/8741
* refactor: system/View/Plugins.php by @mcsaygili in https://github.com/codeigniter4/CodeIgniter4/pull/8742
* refactor: fix method name `ValidationErrors` in View\Plugins by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8758
* refactor: system/Debug/Toolbar/Collectors/Routes.php by @mcsaygili in https://github.com/codeigniter4/CodeIgniter4/pull/8751
* refactor: improve error message in BaseExceptionHandler by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8766
* refactor: FabricatorModel by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8770

## [v4.5.0](https://github.com/codeigniter4/CodeIgniter4/tree/v4.5.0) (2024-04-07)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.8...v4.5.0)

### Breaking Changes

* refactor: always use multiple filters by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7903
* fix: update psr/log to v2 and fix Logger interface by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7967
* fix: incorrect return type for Model::objectToRawArray() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7986
* fix: filter exec order by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7955
* refactor: Remove deprecated Config\Config by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8016
* fix: `FileLocator::findQualifiedNameFromPath()` behavior by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8010
* refactor: remove deprecated methods in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8032
* fix: route options are not merged (outer filter is merged with inner filter) by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8033
* fix: route options are not merged (inner filter overrides outer filter) by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7981
* feat: FileLocator caching by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8017
* refactor: remove deprecated properties and methods in CodeIgniter class by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8050
* fix: make Factories final by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8056
* refactor: remove deprecated test classes by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8057
* refactor: make IncomingRequest::$uri protected by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8067
* refactor: remove deprecated spark commands by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8086
* refactor: remove deprecated Request::isValidIP() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8090
* fix: set_cookie() $expire type by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8080
* fix: remove traditional validation rule param types (1/2) by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8078
* fix: filters are executed when controller does not exist with Auto Routing (Legacy). by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/7925
* fix: remove traditional validation rule param types (2/2) by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8124
* refactor: remove deprecated ModelFactory by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8139
* refactor: remove deprecated properties in Response by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8142
* fix: remove deprecated upper functionality in `Request::getMethod()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8186
* feat: new Required Filters by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8053
* refactor: remove deprecated CastException exception by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8469
* refactor: remove deprecated MockSecurityConfig by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8472
* refactor: remove deprecated CodeIgniter\Entity by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8497
* refactor: remove deprecated Cache\Exceptions\ExceptionInterface by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8498
* fix: API\ResponseTrait can't return string as JSON by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8490
* feat: Validation::run() accepts DB connection by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8499
* feat: 404 Override sets 404 by default by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8535
* refactor: remove deprecated const SPARKED by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8559
* refactor: remove deprecated BaseService::discoverServices() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8589
* fix: move Kint loading to Autoloader by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8603
* feat: add Boot class by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8604

### Fixed Bugs

* fix: error on `Config\Kint` with Config Caching by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8003
* fix: route key lowercase HTTP verbs by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8235
* fix: use `addHeader()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8240
* fix: QueryBuilder limit(0) bug by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8280
* fix: SQLite3 may not throw DatabaseException by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8467
* [4.5] fix: DEBUG-VIEW comments are not output by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8523
* [4.5] fix: $db->dateFormat merge by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8539
* [4.5] fix: spark does not work with composer install --no-dev by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8534
* [4.5] fix: Composer autoload.psr4 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8569
* [4.5] fix: errors when not updating Config\Feature by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8570
* [4.5] fix: TypeError in Filters  by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8683

### New Features

* feat: Language translations finder and update by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/7896

### Enhancements

* feat: domparser - ability to write more advanced expressions by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/7946
* feat: [Validation] Callable Rules by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7933
* perf: autoloader by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8005
* feat: db:table shows db config by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7972
* feat: add `{memory_usage}` replacement by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8008
* perf: replace $locator->getClassname() with findQualifiedNameFromPath() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8012
* feat: add Method/Route logging in exceptionHandler() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8108
* feat: add `config:check` command to check Config vaules by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8110
* feat: one generator command could have multiple views by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8119
* feat: improve CLI input testability by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7978
* feat: add ArrayHelper::dotKeyExists() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8131
* feat: add CSP clearDirective() to clear existing directive by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8220
* feat: [Validation] add `field_exists` rule by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8123
* feat: add Message::addHeader() to add header with the same name by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8194
* feat: `spark filter:check` shows "Required Filters" by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8236
* feat: [Commands] `lang:find` show bad keys when scanning (v2) by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/8285
* feat: add `--dbgroup` option to `spark db:table` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8292
* feat: [Auto Routing Improved] add option to translate uri to camel case by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8321
* feat: `spark routes` shows "Required Filters" by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8237
* feat: HTTP method-aware web page caching by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8364
* feat: `spark make:test` creates test files in `/tests/` directory v2 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8388
* feat: [Routing] add option to pass multiple URI segments to one Controller parameter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8348
* feat: add DataConverter to convert types by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8230
* feat: [Model] add option $updateOnlyChanged by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8455
* feat: add event points for spark commands by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8496
* feat: 404 controller also can get PageNotFoundException message by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8491
* feat: add DB config `dateFormat` to provide default date/time formats by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8525
* feat: use $db->dateFormat in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8538
* feat: permit __invoke() method as Controller default method by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8533
* feat: add Model field casting by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8243
* feat: add spark command to check php.ini by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8581
* feat: improve Redis Session by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8578
* feat: add Config\Optimize by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8605
* feat: support database name with dots by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8664
* feat: add `spark optimize` command by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8610
* feat: add CORS filter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8649
* feat: Support faker modifiers on Fabricator by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8671
* feat: environment-specific Config\Security::$redirect by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8673
* feat: `spark config:check` detects Config Caching by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8711

### Refactoring

* Drop PHP 7.4 support by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7924
* [4.5] refactor: remove unused `use` in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8045
* [4.5] refactor: remove BaseModel assert() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8046
* [4.5] refactor: Filters by rector by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8071
* perf: defer instantiation of Validation in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8087
* refactor: fix types by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8091
* refactor: move ArrayHelper class by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8130
* [4.5] refactor: fix types by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8138
* refactor: fix param types by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8175
* refactor: Validation rule field_exists by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8242
* refactor: `TestResponse` is now a class of its own by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8264
* refactor: fix TypeError in strict mode by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8270
* refactor: add `declare(strict_types=1)` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8072
* refactor: remove deprecated Controller::loadHelpers() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8286
* refactor: remove deprecated methods in Security by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8287
* refactor: HTTP verbs in Router by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8317
* refactor: remove unused exception classes by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8468
* [4.5] refactor: add `declare(strict_types=1)` to ForgeModifyColumnTest by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8502
* [4.5] refactor: use local variables in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8565
* refactor: remove unnecessary BaseService::$services assignment by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8609
* perf: add Factories::get() v2 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8600
* perf: add Services::get() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8607
* refactor: remove deprecated items in Request by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8617
* refactor: followup performance `service()` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/8623
* [4.5] refactor: add declare(strict_types=1) in BadRequestException by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8682
* refactor: DB config properties by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8693
* refactor: upgrade to PHP 8.1 with rector by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8354
* refactor: update PHPUnit to 10 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8069

For the changelog of v4.4, see [CHANGELOG_4.4.md](./CHANGELOG_4.4.md).
