# Changelog

## [v4.6.0](https://github.com/codeigniter4/CodeIgniter4/tree/v4.6.0) (2025-01-19)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.5.8...v4.6.0)

### Breaking Changes

* refactor: remove deprecated failValidationError() in API\ResponseTrait by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8793
* refactor: remove depreacted ResponseInterface::getReason() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8841
* refactor: remove deprecated Logger::cleanFilenames() and TestLogger::cleanup() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8843
* fix: Exception rework by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8728
* fix: DefinedRouteCollector to use RouteCollectionInterface by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8911
* fix: View::renderSection() return type by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8965
* feat: [Filters] enables a filter to run more than once with different arguments by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8977
* fix: add check for duplicate Registrar Auto-Discovery runs by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9073
* fix: Time loses microseconds by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9081
* feat: fix spark db:table causes errors with table name including special chars by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8748
* [4.6] fix: Time::createFromTimestamp() change for PHP 8.4 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9105
* fix: Time::setTimestamp()'s different behavior than DateTime by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9106
* [4.6] fix: inconsistency in detailed error reporting by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9144
* [4.6] feat: force PHP default 32 chars length at 4 bits to Session ID by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9139
* fix: prioritize headers set by the `Response` class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9235

### Fixed Bugs

* [4.6] fix: add validation message for min_dims by @christianberkman in https://github.com/codeigniter4/CodeIgniter4/pull/8988
* fix: [Filters] normalize `$filters` arguments by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8994
### Enhancements
* feat: [FileCollection] add function to reatain multiple patterns by @christianberkman in https://github.com/codeigniter4/CodeIgniter4/pull/8960
* feat: [Validation] add `min_dims` rule in FileRules by @christianberkman in https://github.com/codeigniter4/CodeIgniter4/pull/8966
* feat: add `foundRows` option for MySQLi config by @ducng99 in https://github.com/codeigniter4/CodeIgniter4/pull/8979
* feat: `spark filter:check` shows filter classnames by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8985
* feat: add BaseConnection::resetTransStatus() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8767
* feat: add Services::resetServicesCache() to reset services cache by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9012
* feat: add "400 Bad Request" page for end users by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9044
* feat: add directives to `phpini:check` command by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9117
* feat: multiple hostname routing by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9150
* [4.6] feat: workaround for implicit nullable deprecations in PHP 8.4 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9140
* feat: support CURL HTTP3 by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9145
* feat: design info environment top in `error_exception` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9241
* feat: [Validation] add support for `$dbGroup` as parameter in `is_unique` and `is_not_unique` by @maniaba in https://github.com/codeigniter4/CodeIgniter4/pull/9216
* feat: added the `namespace` option to the `publish` command by @dimtrovich in https://github.com/codeigniter4/CodeIgniter4/pull/9278
* chore: update `Kint` to v6.0 by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9289
* feat: CURL option `force_ip_resolve` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9194
* feat: add SQLite3 config synchronous by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9202
* feat: Differentiate between kilobyte/kibibyte and megabyte/mebibyte by @ThomasMeschke in https://github.com/codeigniter4/CodeIgniter4/pull/9277
* feat: Strict locale negotiation by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9360
* fix: Add support for multibyte strings by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9372
* feat: add page start end total to `PagerRenderer` by @murilohpucci in https://github.com/codeigniter4/CodeIgniter4/pull/9371
* feat: New command `lang:sync`  by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9023
* feat: additional `opcache` setting in check php.ini by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9032

### Refactoring

* [4.6] refactor: Validation rules and tests by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8975
* [4.6] refactor: add `: void` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9013
* refactor: remove dependency on BaseConnection in TableName by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/9104
* refactor: add return type to closuer in FilterCheck by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9190
* refactor: Remove deprecated `RedirectException` by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9399
* refactor: Remove deprecated `EVENT_PRIORITY_*` by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9401
* refactor: Remove deprecated `View::$currentSection` by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9403
* refactor: Remove deprecated `Cache::$storePath` by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9404
* refactor: Remove deprecated `Config\Format::getFormatter()` by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9405
* refactor: Remove deprecation related to cookies by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9406

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

## [v4.4.8](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.8) (2024-04-07)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.7...v4.4.8)

### Fixed Bugs

* fix: [ImageMagickHandler] early terminate processing of invalid library path by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8680
* docs: fix PHPDoc types in BaseModel by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8679
* fix: the error view is determined by Exception code by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8689
* fix: `Pager::only([])` does not work by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8702
* refactor: remove unneeded code in SQLite3\Table and fix PHPDoc types in Database by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8703
* docs: fix return type in BaseResult by @Pebryan354 in https://github.com/codeigniter4/CodeIgniter4/pull/8709

### Refactoring

* refactor: simplify ImageMagickHandler::getVersion() by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8681
* refactor: [Rector] Apply ExplicitBoolCompareRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/8704

## [v4.4.7](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.7) (2024-03-29)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.6...v4.4.7)

### SECURITY

* **Language:** *Language class DoS Vulnerability* was fixed. See the
  [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-39fp-mqmm-gxj6)
  for more information.
* **URI Security:** The feature to check if URIs do not contain not permitted
  strings has been added. This check is equivalent to the URI Security found in
  CodeIgniter 3. This is enabled by default, but upgraded users need to add
  a setting to enable it.
* **Filters:** A bug where URI paths processed by Filters were not URL-decoded
  has been fixed.

### Breaking Changes
* fix: Time::difference() DST bug by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8661

### Fixed Bugs
* fix: [Validation] FileRules cause error if getimagesize() returns false by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8592
* fix: isWriteType() to recognize CTE; always excluding RETURNING by @markconnellypro in https://github.com/codeigniter4/CodeIgniter4/pull/8599
* fix: duplicate Cache-Control header with Session by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8601
* fix: [DebugBar] scroll to top by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/8595
* fix: Model::shouldUpdate() logic by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8614
* fix: esc() for 'raw' context by @Cleric-K in https://github.com/codeigniter4/CodeIgniter4/pull/8633
* docs: fix incorrect CURLRequest allow_redirects description by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8653
* fix: Model::set() does not accept object by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8670

### Refactoring
* refactor: replace PHP_VERSION by PHP_VERSION_ID by @justbyitself in https://github.com/codeigniter4/CodeIgniter4/pull/8618
* refactor: apply early return pattern by @justbyitself in https://github.com/codeigniter4/CodeIgniter4/pull/8621
* refactor: move footer info to top in error_exception.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8626

## [v4.4.6](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.6) (2024-02-24)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.5...v4.4.6)

### Breaking Changes

* fix: Time::createFromTimestamp() returns Time with UTC by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8544

### Fixed Bugs

* fix: [OCI8] getFieldData() returns incorrect `default` value by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8459
* fix: [SQLite3] getFieldData() returns incorrect `primary_key` values by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8460
* fix: [OCI8][Postgre][SQLSRV][SQLite3] change order of properties returned by getFieldData() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8481
* docs: fix supported SQL Server version by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8489
* fix: [SQLite3] Forge::modifyColumn() messes up table by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8457
* docs: fix incorrect @return type in `ResultInterface-getCustomRowObject()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8503
* fix: [Postgre] updateBatch() breaks `char` type data by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8524
* fix: DebugBar block by CSP by @YapsBridging in https://github.com/codeigniter4/CodeIgniter4/pull/8411
* docs: fix `@phpstan-type` in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8543
* fix: [CURLRequest] Multiple HTTP 100 return by API. by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/8466
* fix: PHPDoc types in controller.tpl.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8561
* fix: [Session] Redis session race condition by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8323

### Refactoring

* test: refactor ImageMagickHandlerTest by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8461
* test: refactor GetFieldDataTest by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8480
* refactor: use ternary operators in Helpers by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/8529
* refactor: use official site URLs by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8541
* refactor: remove redundant URL helper loading by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8556
* refactor: small improvement in `loadInNamespace` Autoloader by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/8553

## [v4.4.5](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.5) (2024-01-27)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.4...v4.4.5)

### Fixed Bugs

* fix: bug 4.4.4 `spark serve` not working when using Session in Routes.php by @ALTITUDE-DEV-FR in https://github.com/codeigniter4/CodeIgniter4/pull/8389
* fix: `highlightFile()` in `BaseExceptionHandler` for PHP 8.3 by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/8401
* fix: [Validation] DotArrayFilter returns incorrect array when numeric index array is passed by @grimpirate in https://github.com/codeigniter4/CodeIgniter4/pull/8425
* fix: OCI8 Forge always sets NOT NULL when BOOLEAN is specified by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8440
* fix: DB Seeder may use wrong DB connection during testing by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8447
* fix: [Postgre] QueryBuilder::updateBatch() does not work (No API change) by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8439
* fix: [Postgre] QueryBuilder::deleteBatch() does not work by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8451
* fix: [Email] setAttachmentCID() does not work with buffer string by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8446
* fix: add undocumented Model $allowEmptyInserts by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8456

### Refactoring

* refactor: remove overrides for coding-standard v1.7.12 by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8386
* refactor: Table class to fix phpstan errors by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8402
* fix: typo in pager default_simple by @jasonliang-dev in https://github.com/codeigniter4/CodeIgniter4/pull/8407
* refactor: improve Forge variable names by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8434

## [v4.4.4](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.4) (2023-12-28)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.3...v4.4.4)

### Breaking Changes

* fix: Validation rule with `*` gets incorrect values as dot array syntax by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8129
* fix: validation rule `matches` and `differs` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8122
* fix: [CURLRequest] skip hostname checks if options 'verify' false by @NicolaeIotu in https://github.com/codeigniter4/CodeIgniter4/pull/8258
* fix: get_filenames() does not follow symlinks by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8298

### Fixed Bugs

* fix: change make:command default $group to `App` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8109
* fix: typo in help message in `spark filter:check` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8118
* fix: Hot reloading when session is enabled by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/8112
* fix: make:cell help message by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8133
* fix: [DebugBar] dark mode timeline "Controller" by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8125
* fix: PHPDoc types in controller.tpl.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8144
* fix: `@return` in filter.tpl.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8145
* fix: when request body is `0`, $body will be null by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8161
* fix: `spark routes` outputs `<unknown>` only when {locale} with `useSupportedLocalesOnly(true)` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8167
* fix: Undefined array key error in `spark db:table` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8173
* fix: force_https() redirects to wrong URL when baseURL has subfolder by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8191
* fix: Validation raises TypeError when invalid JSON comes by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8153
* fix: FilterTestTrait Undefined variable $filterClasses by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8195
* fix: Image::save() causes error with webp by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8210
* fix issue where running FileLocator::getClassname() on a directory would cause a PHP error by @colethorsen in https://github.com/codeigniter4/CodeIgniter4/pull/8216
* fix: make Request::getEnv() deprecated by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8234
* fix: ExceptionHandler displays incorrect Exception classname by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8239
* fix: [Cache] Double prefix for increment in FileHandler by @il-coder in https://github.com/codeigniter4/CodeIgniter4/pull/8255
* docs: fix Database Utility Class `getXMLFromResult()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8276
* fix: autoload helpers in test bootstrap by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8275
* fix: Model handling of Entity $primaryKey casting  by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8282
* fix: Handle non-array JSON in validation by @woodongwong in https://github.com/codeigniter4/CodeIgniter4/pull/8288
* fix: DEPRECATED error in Honeypot by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8316
* fix: [Auto Routing Improved] `spark routes` shows incorrect routes when translateURIDashes is enabled by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8320
* fix: migrations not using custom DB connection of migration runner by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8221
* Always return a new instance of a Cell by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/8330
* fix: DOMParser cannot see element with `id="0"` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8360

### Refactoring

* [Rector] Apply SingleInArrayToCompareRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/8102
* refactor: RedisHandler ttl() calls by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8155
* [Testing] Use assertEqualsWithDelta() when possible by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/8158
* refactor: replace non-boolean if conditions in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8193
* refactor: View classes to fix PHPStan errors by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8208
* refactor: Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8260
* replace -1 with E_ALL in error_reporting calls by @ThomasMeschke in https://github.com/codeigniter4/CodeIgniter4/pull/8212
* refactor: apply SimplifyEmptyCheckOnEmptyArrayRector by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8341
* refactor: apply DisallowedEmptyRuleFixerRector by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8344
* refactor: rely on $config property in ViewDecoratorTrait by @mostafakhudair in https://github.com/codeigniter4/CodeIgniter4/pull/8021
* refactor: replace empty() Part 1 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8345

## [v4.4.3](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.3) (2023-10-26)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.2...v4.4.3)

### SECURITY

* *Detailed Error Report is Displayed in Production Environment* was fixed. See the [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-hwxf-qxj7-7rfj) for more information.

### Fixed Bugs

* fix: FilterTestTrait::getFilterCaller() does not support Filter classes as array by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8058
* fix: add dbgroup to model template only when specified as an option by @sammyskills in https://github.com/codeigniter4/CodeIgniter4/pull/8077
* Update phpstan-codeigniter and fix errors on Modules by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/8036
* fix: [Validation] exact_length does not pass int values by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8088
* fix: [Table] field named `data` will produce bugged output by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/8054
* docs: fix event points descriptions by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8076
* docs: fix helper loading by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8084

## [v4.4.2](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.2) (2023-10-19)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.1...v4.4.2)

### Fixed Bugs

* Fix: [Session] the problem of secondary retrieving values ​​in RedisHandler by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/7887
* fix: `spark migrate` `-g` option by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7894
* fix: [DebugBar] dark mode `timeline-color-open` color text on `Debug` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/7907
* fix: base_url()/site_url() does not work on CLI by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7901
* Fix: Routing::loadRoutes() in windows do not validate correctly $routesFiles by @pjsde in https://github.com/codeigniter4/CodeIgniter4/pull/7930
* fix: Services::request() should call AppServices instead static by @pjsde in https://github.com/codeigniter4/CodeIgniter4/pull/7985
* fix: lang() may return false by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7966
* fix: CI returns "200 OK" when PageNotFound by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8011
* fix: spark may not show exceptions or show backtrace as json by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7962
* fix: CLI prompt validation message by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7977
* fix: CSP style nonce is added even if honeypot is not attached by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8025
* fix: named routes don't work with spark by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8028
* fix: add a primary key to an existing table by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/8031
* fix: reverse route for `''` is not `false` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8024
* fix: `spark routes` may show incorrect route names by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8040
* fix: Factories caching bug by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8037
* fix: file sort order in Files DebugBar by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8048

### Enhancements

* fix: check for CSRF token in the raw body by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/7915

### Refactoring

* fix: add types to View $filters and $plugins by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/7885
* test: use PHP_VERSION_ID instead of PHP_VERSION by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7913
* [PHP 8.3] refactor: ReflectionProperty::setValue() signature deprecation by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7911
* refactor: remove unneeded arguments to session by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/7919
* fix: types for common functions by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/7917
* Refactor: Apply PHPStan rule "Short ternary operator is not allowed" to RouteCollection by @pjsde in https://github.com/codeigniter4/CodeIgniter4/pull/7947
* refactor: remove $_SESSION from methods and functions by @pjsde in https://github.com/codeigniter4/CodeIgniter4/pull/7982
* refactor: if condition in OCI8/Connection.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7994
* style: remove unnecessary () in Toolbar by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8013
* refactor: replace deprecated `Services::request(config, false)` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7998
* refactor: delete duplicate code for Composer loading by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/8004
* [Rector] Apply BooleanInIfConditionRuleFixerRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/7951

## [v4.4.1](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.1) (2023-09-05)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.0...v4.4.1)

### Fixed Bugs

* docs: add missing Config updates for Hot Reloading by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7862
* fix: auto route legacy does not work by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7871
* fix: Factories may not return shared instance by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7868
* fix: replace `config(DocTypes::class)` with `new DocTypes()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7872
* fix: FeatureTest may cause risky tests by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7867
* fix: reverse routing causes ErrorException by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7880
* fix: Email library forces to switch to TLS when setting port 465 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7883
* fix: [DebugBar] make CSS rotate class less broad by @sanchawebo in https://github.com/codeigniter4/CodeIgniter4/pull/7882
* fix: FeatureTest fails when forceGlobalSecureRequests is true by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7890

## [v4.4.0](https://github.com/codeigniter4/CodeIgniter4/tree/v4.4.0) (2023-08-25)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.3.8...v4.4.0)

### Breaking Changes

* fix: URI::setSegment() accepts the last +2 segment without Exception by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7251
* feat: custom exception handler by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7087
* Clean router config by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/7380
* feat: add ValidationInterface::getValidated() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7429
* [4.4] refactor: moving RedirectException. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/7545
* Remove Config\App Session items by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7255
* perf: RouteCollection $routes optimization by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7175
* Remove Config\App Security items by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7630
* refactor: extract ResponseCache class for Web Page Caching by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7644
* fix: change Services::session() config param type by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7671
* feat: add Factories::define() to explicitly override a class by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7733
* Return signatures of Autoloader's loaders should be void by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/7747
* fix: remove instantiation of Response in `Services::exceptions()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7670
* refactor: move callExit() to index.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7800
* rework: URI creation and URL helper by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7282

### Fixed Bugs

* fix: incorrect segment number in URI::getSegment() exception message by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7267
* fix: can't change and override valid locales by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7309
* fix: Validation::check() does not accept array rules by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7424
* fix: directory separator from routing file. by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/7487
* [4.4] Fix output buffering by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/7500
* fix: [Auto Routing Improved] one controller method has more than one URI when $translateURIDashes is true by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7422
* fix: [4.4] merge Exception::maskSensitiveData() fix into BaseExceptionHandler by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7736

### New Features

* feat: Hot Reloading by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/7489

### Enhancements

* feat: `renderSection` option to retained data by @addngr in https://github.com/codeigniter4/CodeIgniter4/pull/7126
* feat: [Auto Routing Improved] fallback to default method by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7162
* feat: Filter Arguments with $filters in Config\Filters by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7159
* feat: New method DownloadResponse::inline() by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/7207
* feat: add `--host` option to `spark routes` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7213
* feat: add `Entity::injectRawData()` to avoid name collision by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7208
* feat: [MySQLi] add config to use MYSQLI_OPT_INT_AND_FLOAT_NATIVE by @kai890707 in https://github.com/codeigniter4/CodeIgniter4/pull/7265
* feat: add new setter/getter for Entity by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7230
* feat: [SQLSRV] getFieldData() supports nullable by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7301
* feat: HTML Table data keys synchronize order with Heading keys by @rumpfc in https://github.com/codeigniter4/CodeIgniter4/pull/7409
* feat: [Validation] add method to get the validated data by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7420
* feat: [Auto Routing Improved] Module Routing by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7416
* feat: function array_group_by by @rumpfc in https://github.com/codeigniter4/CodeIgniter4/pull/7438
* feat: add Session::close() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7508
* feat: `GDHandler` make `WebP` with option quality by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/7506
* feat: [Auto Routing Improved] fallback to default controller's default method by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7406
* Add access to `full_path` index of uploaded files by @JamminCoder in https://github.com/codeigniter4/CodeIgniter4/pull/7541
* [4.4] Rework redirect exception by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/7610
* feat: [CURLRequest] add option for Proxy by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7632
* feat: improve View route output by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7646
* feat: add SiteURI class by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7252
* feat: add SiteURIFactory by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7256
* feat: [Factories] Config caching by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7696

### Refactoring

* refactor: remove Cookie config items in Config\App by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7221
* refactor: deprecate $request and $response in Exceptions::__construct() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7232
* refactor: use config(Cache::class) in CodeIgniter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7297
* [4.4] refactor: a single point of sending the Response. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/7519
* refactor: [Entity] fix incorrect return value by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7544
* [4.4] refactor: use ::class to config() param by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7619
* refactor: drop support for `Config\App::$proxyIPs = ''` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7621
* refactor: extract DefinedRouteCollector by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7653
* refactor: remove uneeded `if` in Commands\Utilities\Routes by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7661
* refactor: [4.4] add types for phpstan by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7723
* Remove trimming logic of `Autoloader::loadClass()` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/7763

See [CHANGELOG_4.3.md](./changelogs/CHANGELOG_4.3.md)
