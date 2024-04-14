# Changelog

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
