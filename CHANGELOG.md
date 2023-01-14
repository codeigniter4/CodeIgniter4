# Changelog

## [v4.3.1](https://github.com/codeigniter4/CodeIgniter4/tree/v4.3.1) (2023-01-14)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.3.0...v4.3.1)

### Fixed Bugs

* fix: Email config in the .env doesn't appear as expected by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7078
* fix: TypeError in Validation is_unique/is_not_unique by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7085
* fix: revert method name resetQuery() changed accidentally by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7083
* fix: handling float in Validation Strcit Rules (greater_than, greater_than_equal_to, less_than, less_than_equal_to) by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7098
* docs: add missing instruction for Config/Exceptions in PHP 8.2 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7100
* fix: Call to undefined method Composer\InstalledVersions::getAllRawData() error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7107

### Refactoring

* [Rector] Enable AddDefaultValueForUndefinedVariableRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/7088

## [v4.3.0](https://github.com/codeigniter4/CodeIgniter4/tree/v4.3.0) (2023-01-10)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.12...v4.3.0)

### Breaking Changes

* fix: throws DatabaseException in DB connections by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6163
* config: DB Error always throws Exception CI_DBUG by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6183
* Config Property Types by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6214
* refactor: loading app/Config/routes.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6293
* fix: exceptionHandler may return invalid HTTP status code by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6228
* feat: add Form helpers for Validation Errors by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6384
* fix: ValidationInterface by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6253
* fix: types in database classes by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6527
* fix: ResponseInterface (1) by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6556
* Improve BaseConnection::getForeignKeyData() and Forge::addForeignKey() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6468
* Refactor BaseBuilder *Batch() Methods by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6536
* refactor: remove `type="text/javascript"` in <script> tag by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6606
* fix: ResponseInterface (2) by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6569
* Add ability to set index names by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6552
* fix: MessageInterface inheritance by @MGatner in https://github.com/codeigniter4/CodeIgniter4/pull/6695
* fix: add missing getProtocolVersion() in MessageInterface by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6702
* Add Forge::processIndexes() to create indexes on existing table by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6676
* fix: add missing ResultInterface::getNumRows() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6778
* feat: add OutgoingRequestInterface by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6698
* fix: make Time immutable by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6771
* feat: disallow `Model::update()` without WHERE clause by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6883
* feat: do not throw exceptions during transactions by default by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6917
* fix: don't change the variable type and filter all values in JSON request by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6954

### Fixed Bugs

* fix: SecurityException's HTTP status code by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6408
* Fix UpsertTest::testUpsertWithMultipleSet by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6692
* fix: support for assigning extra data for the view() method in controlled cells by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6681
* Fix testMode() with upsert() and getCompiledUpsert() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6697
* Fix BaseBuilder setAlias() and RawSql use with key value pairs by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6741
* fix: BasePreparedQuery class to return boolean values for write-type queries by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6750
* fix: Time::now() does not respect timezone when using setTestNow() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6752
* fix: remove CI_DEBUG check in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6863
* fix: BaseBuilder::getOperator() doesn't recognize LIKE operator in array expression by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6986
* fix: Honeypot field appears when CSP is enabled by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7029

### Enhancements

* Feature: Adding StreamFilterTrait by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6112
* feat: add routes useSupportedLocalesOnly property by @pjsde in https://github.com/codeigniter4/CodeIgniter4/pull/6073
* Feat add events for insertBatch()/updateBatch() by @pjsde in https://github.com/codeigniter4/CodeIgniter4/pull/6125
* feat: improve namespaces command by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6142
* feat: add method to insert empty data in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6109
* feat: Autoloader::sanitizeFilename() throws Exception by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6193
* Language: Make production error message translatable (replaces #6197) by @sba in https://github.com/codeigniter4/CodeIgniter4/pull/6235
* feat: add methods to modify files in Publisher by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6133
* SQLite3 Connection getIndexData() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6221
* feat: `spark filter:check` command by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6283
* feat: Encryption CI3 compatibility by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6277
* feat: `spark routes` shows route name by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6271
* error_404.php translation and design by @sba in https://github.com/codeigniter4/CodeIgniter4/pull/6288
* feat: make `CLI::input()` testable by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6335
* Feature for Timer to measure callable performance by @rumpfc in https://github.com/codeigniter4/CodeIgniter4/pull/6321
* feat: add IntBoolCast for Entity by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6348
* Add new method `promptByMultipleKeys()` in CLI class by @rivalarya in https://github.com/codeigniter4/CodeIgniter4/pull/6302
* Allow calling help info using `spark --help` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6383
* feat: autoload helpers by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6397
* Add RawSql to BaseConnection->escape() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6332
* feat: add locale param to `route_to()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6448
* Postgre & SQLSRV - Should Never Have A Field Length For TEXT by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6405
* [4.3] Fix tests. Changed StreamFilterTrait and CITestStreamFilter. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6450
* Create Forge::dropPrimaryKey() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6488
* feat: add manual config for Composer package auto-discovery by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6503
* Added view() method to route collections by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6568
* When/WhenNot methods for db in a trait by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/6574
* Allow Cells to be auto-located within */Cells directories by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/6601
* Decamelize function by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/6615
* feat: Controlled Cells by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/6620
* Allow HTTP/3 to work and not be blocked. by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/6595
* feat: add method to disable controller filters by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6652
* feat: implementation option http2 in `CURLRequest` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/6679
* Add BaseBuilder::upsert() and BaseBuilder::upsertBatch() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6600
* Deallocate prepared statements by @fpoy in https://github.com/codeigniter4/CodeIgniter4/pull/6665
* feat: Check logs against parts of the message only by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6704
* feat: Opt-in logging of deprecations by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6705
* feat: void element tags in helpers are selectable between `>` and `/>` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/6789
* feat: add $allowedHostnames for multiple domain support by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6785
* new make:cell command  by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/6864
* Add BaseBuilder::deleteBatch() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6734
* Update Kint to 5.0.1 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6893
* Add `is_windows()` global function by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6884
* fix: HTML output by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6899
* feat: add SQLite3 Config busyTimeout by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6939
* insertBatch updateBatch upsertBatch deleteBatch from query by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6689
* feat: add IncomingRequest::getRawInputVar() method by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6943
* feat: add closure validation rule by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6951
* refactor: add Config\Session by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6989
* feat: add IncomingRequest::is() method by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6995
* feat: `spark routes` option to sort by handler by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7015

### Refactoring

* Extracting the call handler for Spark commands from kernel. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6110
* chore: move Kint to `require-dev` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6449
* Improve `BaseBuilder::updateBatch()` SQL by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6373
* refactor: to fix psalm errors by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6535
* Add template types to `Connection` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6709
* refactor: around URI by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6784
* Add template types to Result by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6749
* refactor: make now() testable by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6753
* refactor: remove Workaround for Faker deprecation errors in PHP 8.2 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6987
* refactor: to fix psalm error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6999

## [v4.2.12](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.12) (2023-01-09)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.11...v4.2.12)

### Fixed Bugs
* docs: fix request.rst by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7014
* fix: `link_tag()` missing `type="application/rss+xml"` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7022
* fix: Request::getIPaddress() causes error on CLI by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7030
* docs: fix upgrade_database.rst by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7036
* fix: `spark migrate:status` shows incorrect filename when format is `Y_m_d_His_` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7038
* fix: Model::save() object when useAutoIncrement is disabled by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/7042
* fix: define of STDOUT in CLI init() method by @jozefrebjak in https://github.com/codeigniter4/CodeIgniter4/pull/7052
* fix: change `getFile()` function of \CodeIgniter\Events\Events to static. by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/7046
* fix: [Email] add fallback to use gethostname() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7053
* Fixing bug with legacy autoRoute when testing by @baycik in https://github.com/codeigniter4/CodeIgniter4/pull/7060

### Refactoring
* refactor: RequestTrait by rector by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7006
* refactor: update sass output by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/7026

## [v4.2.11](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.11) (2022-12-21)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.10...v4.2.11)

### SECURITY
* *Attackers may spoof IP address when using proxy* was fixed. See the [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-ghw3-5qvm-3mqc) for more information.
* *Potential Session Handlers Vulnerability* was fixed. See the [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-6cq5-8cj7-g558) for more information.

### Fixed Bugs
* fix:  Request::getIPAddress() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6820
* fix: Model cannot insert when $useAutoIncrement is false by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6827
* fix: View Parser regexp does not support UTF-8 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6835
* Handle key generation when key is not present in .env by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6839
* Fix: Controller Test withBody() by @MGatner in https://github.com/codeigniter4/CodeIgniter4/pull/6843
* fix: body assigned via options array in CURLRequest class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6854
* Fix CreateDatabase leaving altered database config in connection by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6856
* fix: cast to string all values except arrays in Header class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6862
* add missing @method Query grouping in Model by @paul45 in https://github.com/codeigniter4/CodeIgniter4/pull/6874
* fix: `composer update` might cause error "Failed to open directory" by @LeMyst in https://github.com/codeigniter4/CodeIgniter4/pull/6833
* fix: required PHP extentions by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6897
* fix: Use Services for the FeatureTestTrait request. by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/6966
* fix: FileLocator::locateFile() bug with a similar namespace name by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6964
* fix: socket connection in RedisHandler class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6972
* fix: `spark namespaces` cannot show a namespace with mutilple paths by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6977
* fix: Undefined constant "CodeIgniter\Debug\VENDORPATH" by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6985
* fix: large HTTP input crashes framework by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6984
* fix: empty paths for `rewrite.php` by @datamweb in https://github.com/codeigniter4/CodeIgniter4/pull/6991
* fix: `PHPStan` $cols not defined in `CLI` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/6994
* Fix MigrationRunnerTest for Windows by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6855
* fix: turn off `Xdebug` note when running phpstan by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/6851
* Fix ShowTableInfoTest to pass on Windows by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6853
* Fix MigrateStatusTest for Windows by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6866
* Fix ShowTableInfoTest when migration records are numerous by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6868
* Fix CreateDatabaseTest to not leave database by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6867
* Fix coverage merge warning by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6885
* fix: replace tabs to spaces  by @zl59503020 in https://github.com/codeigniter4/CodeIgniter4/pull/6898
* fix: slack links by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6907
* Fix typo in database/queries.rst by @philFernandez in https://github.com/codeigniter4/CodeIgniter4/pull/6920
* Fix testInsertWithSetAndEscape to make not time dependent by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6974
* fix: remove unnecessary global variables in rewrite.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6973

## [v4.2.10](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.10) (2022-11-05)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.9...v4.2.10)

### Fixed Bugs
* docs: fix PHPDoc types in Session by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6796
* fix: output "0" at the end of toolbar js when Kint::$enabled_mode is false by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6809

### Refactoring
* Refactor assertHeaderEmitted and assertHeaderNotEmitted by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6806
* fix: variable types for PHPStan 1.9.0 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6810

## [v4.2.9](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.9) (2022-10-30)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.8...v4.2.9)

**Hotfix release to fix PHPUnit errors (see https://github.com/codeigniter4/CodeIgniter4/pull/6794)**

## [v4.2.8](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.8) (2022-10-30)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.7...v4.2.8)

### Fixed Bugs
* Fix DotEnv class turning `export` to empty string by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6625
* Remove unneeded `$logger` property in `Session` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6647
* fix: Add missing CLIRequest::getCookie() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6646
* fix: routes registration bug by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6644
* Bug: showError in CLI/BaseCommand use hardcoded error view path by @fpoy in https://github.com/codeigniter4/CodeIgniter4/pull/6657
* fix: getGetPost() and getPostGet() when index is null by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6675
* fix: add missing methods to BaseConnection by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6712
* fix: bug that esc() accepts invalid context '0' by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6722
* fix: [Postgres] reset binds when replace() method is called multiple times in the context by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6728
* fix: [SQLSRV] _getResult() return object for preparedQuery class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/6718
* Fix error handler callback by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6724
* bug: Supply mixin for TestResponse by @MGatner in https://github.com/codeigniter4/CodeIgniter4/pull/6756
* fix: CodeIgniter::run() doesn't respect $returnResponse by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6737
* Bug: ResponseTest::testSetLastModifiedWithDateTimeObject depends on time by @fpoy in https://github.com/codeigniter4/CodeIgniter4/pull/6683
* fix: workaround for Faker deprecation errors in PHP 8.2 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6758
* Add .gitattributes to framework by @totoprayogo1916 in https://github.com/codeigniter4/CodeIgniter4/pull/6774
* Delete admin/module directory by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6775

## [v4.2.7](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.7) (2022-10-06)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.6...v4.2.7)

### SECURITY
* *Secure or HttpOnly flag set in Config\Cookie is not reflected in Cookies issued* was fixed. See the [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-745p-r637-7vvp) for more information.

### Breaking Changes
* fix: make Time::__toString() database-compatible on any locale by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6461
* fix: set_cookie() does not use Config\Cookie values by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6544
* fix: `required_without` rule logic in `Validation` class. by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/6589

### Fixed Bugs
* fix: typos in messages in Language/en/Email.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6517
* fix: table attribute cannot applied on td element by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/6538
* add: set up "script_name" to handle every request by index.php file. by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/6522
* fix: CSP autoNonce = false by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6570
* fix: inconsistent new line view in `date_helper` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/6582
* fix: safe_mailto() does not work with CSP by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6604
* fix: script_tag() does not work with CSP by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6611
* fix: `$cleanValidationRules` does not work in Model updates by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6588
* Fixed a bug that URLs with trailing newlines do not become invalid in validation. by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/6618
* fix: missing `valid_json` in Validation Language by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/6624
* fix: default values for Session Redis Handler by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6614

### Enhancements
* Update coding-standards version by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6537
* chore: update ThirdParty Kint to 4.2.2 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6583

### Refactoring
* Refactor: CodeIgniter::generateCacheName() by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6498
* refactor: replace `global $app` with Services by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/6524
* refactor: small refactoring in view() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6546
* refactor: replace utf8_encode() with mb_convert_encoding() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6564
* refactor: make $precision int in View Filter round by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6566

## [v4.2.6](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.6) (2022-09-04)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.5...v4.2.6)

### Fixed Bugs
* fix: AssertionError occurs when using Validation in CLI by @daycry in https://github.com/codeigniter4/CodeIgniter4/pull/6452
* fix: [Validation] JSON data may cause "Array to string conversion" error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6467
* Fix fatal error gets turned to `0` severity on shutdown handler by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6472
* Fix redis cache increment/decrement methods by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6473
* Fix broken caching system when array of allowed parameters used by @JavaDeveloperKiev in https://github.com/codeigniter4/CodeIgniter4/pull/6475
* fix: Strict Validation Rules greater_than/less_than by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6492

### Refactoring
* refactor: fix PHPStan errors by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6470
* Bump `friendsofphp/php-cs-fixer` to `~3.11.0` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6471
* Fix overlooked coding style violations by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6491

## [v4.2.5](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.5) (2022-08-28)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.4...v4.2.5)

### Breaking Changes
* Add $cached param to BaseConnection::tableExists() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6364
* Fix validation custom error asterisk field by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/6378

### Fixed Bugs
* fix: Email class may not log an error when it fails to send by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6362
* fix: Response::download() causes TypeError by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6361
* fix: command usages by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6402
* Fix: The subquery adds a prefix for the table alias. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6390
* Fix Sqlite Table::createTable() by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6396
* docs: add missing `@method` `groupBy()` in Model by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6433
* fix: CLIRequest Erros in CLI by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6421
* fix: Call to undefined method CodeIgniter\HTTP\CLIRequest::getLocale() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6442

### Enhancements
* chore: update Kint to 4.2.0 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6436

### Refactoring
* refactor: add test for DownloadResponse by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6375
* refactor: ValidationTest by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6382
* refactor: remove unused `_parent_name` in BaseBuilder::objectToArray() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6427
* Remove unneeded abstract `handle()` method by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6434

## [v4.2.4](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.4) (2022-08-13)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.3...v4.2.4)

**Hotfix release to fix download errors (see https://github.com/codeigniter4/CodeIgniter4/pull/6361)**

## [v4.2.3](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.3) (2022-08-06)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.2...v4.2.3)

* SECURITY: Improve CSRF protection (for Shield CSRF security fix)

## [v4.2.2](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.2) (2022-08-05)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.1...v4.2.2)

### Breaking Changes
* fix: when running on CLI, two Request objects were used in the system by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6089
* fix: Builder insert()/update() does not accept an object by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6216
* fix: create table if not exists when indexes already exist by @sclubricants in https://github.com/codeigniter4/CodeIgniter4/pull/6249
* fix: page cache saves Response data before running after filters by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6282
* fix: random_string('crypto') may return string less than $len or ErrorException by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6334

### Fixed Bugs
* Fixed: BaseBuilder increment/decrement do not reset state after a query by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6146
* fix: SQLite3\Connection\getIndexData() error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6152
* fix: `is_image` causes PHP 8.1 deprecated error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6157
* fix: prepared query is executed when using QueryBuilder by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6164
* fix: Time::getAge() calculation by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6159
* fix: Session cookies are sent twice with Ajax by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6167
* fix: QueryBuilder breaks select when escape is false by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5118
* fix: PHPDoc return type in ControllerTestTrait methods by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6168
* fix: `$routes->group('/', ...)` creates the route `foo///bar` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6186
* fix: use lang('HTTP.pageNotFound') on production 404 page by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6202
* fix: BaseConnection may create dynamic property by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6198
* fix: Email SMTP may throw Uncaught ErrorException by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6184
* fix: CSP reportOnly behavior by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6201
* fix: lang() causes Error on CLI by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6209
* fix: multiple pagers with models do not work by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6211
* fix: tweak empty line output of `spark db:table` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6215
* fix:  custom validation error is cleared when calling setRule() twice by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6241
* Fix: Validation of fields with a leading asterisk. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6243
* fix: Call to undefined method CodeIgniter\Pager\PagerRenderer::getDetails() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6251
* fix: exceptionHandler may cause HTTPException: Unknown HTTP status code by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6254
* fix: invalid INSERT/DELETE query when Query Builder uses table alias by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5376
* fix: Add db port entry into env file. by @nalakapws in https://github.com/codeigniter4/CodeIgniter4/pull/6250
* fix: update `.gitattributes` by @totoprayogo1916 in https://github.com/codeigniter4/CodeIgniter4/pull/6256
* fix: format_number() can't be used on CLI by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6263
* fix: add parameter checking for max_size by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6261
* fix: route name is not displayed in Exception message by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6269
* fix: `spark routes` shows 404 error when using regex by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6279
* fix: Entity::hasChanged() returns wrong result to mapped property  by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6285
* fix: unable to add more than one file to FileCollection constructor by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6291
* fix: Security::derandomize() may cause hex2bin() error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6292
* fix: use getenv() instead of $_SERVER in detectEnvironment() by @fcosrno in https://github.com/codeigniter4/CodeIgniter4/pull/6257
* fix: OCI8 uses deprecated Entity by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6323
* fix: Parse error occurs before PHP version check by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6327
* fix:  404 page might display Exception message in production environment by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6333

### Refactoring
* refactor: replace $e->getMessage() with $e in log_message() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6182
* refactor: add CompleteDynamicPropertiesRector by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6187
* refactor: debug toolbar by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6272
* refactor: Exception exit code by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6286
* chore: Remove Vagrant by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6314
* refactor: CSRF protection by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6320

## [v4.2.1](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.1) (2022-06-16)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.2.0...v4.2.1)

### Breaking Changes
* Fix MIME guessing of extension from type by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6059
* fix: get_cookie() may not use the cookie prefix by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6082

### Fixed Bugs
* fix: get_cookie() does not take Config\Cookie::$prefix by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6080
* fix: session cookie name bug by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6091
* fix: Session Handlers do not take Config\Cookie by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6081
* fix: reverse routing does not work with full classname starting with `\` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6104
* fix: insert error message in QueryBuilder by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6108
* fix: `spark routes` shows "ERROR: 404" by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6098
* fix: Time::setTestNow() does not work with fa Locale by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6116
* fix: `migrate --all` causes `Class "SQLite3" not found` error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6117
* fix: event DBQuery is not fired on failed query when DBDebug is true by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6127
* fix: `Time::humanize()` causes error with ar locale by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6120
* Fix decorators by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/6090
* Fix lost error message by test when after testInsertResultFail. by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/6113
* test: fix forgetting to restore DBDebug value by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6115

### Refactoring
* Apply AutoRouterImproved::translateURIDashes() by @pjsde in https://github.com/codeigniter4/CodeIgniter4/pull/6084
* Remove useless catch by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6095
* Move preload.php example to starter app by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/6088
* style: compile sass by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6099

## [v4.2.0](https://github.com/codeigniter4/CodeIgniter4/tree/v4.2.0) (2022-06-03)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.9...v4.2.0)

### Breaking Changes
* Validation: support placeholders for anything by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5545
* Fix: Validation. Error key for field with asterisk by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5609
* Improve exception logging by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5684
* fix: spark can't use options on PHP 7.4 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5836
* fix: [Autoloader] Composer classmap usage by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5850
* fix: using multiple CLI::color() in CLI::write() outputs strings with wrong color by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5893
* refactor: [Router] extract a class for auto-routing by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5877
* feat: Debugbar request microtime by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5958
* refactor: `system/bootstrap.php` only loads files and registers autoloader by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5972
* fix: `dot_array_search()` unexpected behavior by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5940
* feat: QueryBuilder join() raw SQL string support by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5875
* fix: change BaseService::reset() $initAutoloader to true by default by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6020

### Fixed Bugs
* chore: update admin/framework/composer.json Kint by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5531
* fix: BaseConnection::getConnectDuration() number_format(): Passing null to parameter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5536
* Fix: Debug toolbar selectors by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5544
* Fix: Toolbar. ciDebugBar.showTab() context. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5554
* Refactor Database Collector display by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5553
* fix: add missing Migration lang item by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5557
* feat: add Validation Strict Rules by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5445
* fix: `Time::createFromTimestamp()` sets incorrect time when specifying timezone by @totoprayogo1916 in https://github.com/codeigniter4/CodeIgniter4/pull/5588
* fix: Entity's isset() and unset() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5497
* Fix: Deletion timestamp of the Model is updated when a record that has been soft-deleted is deleted again by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5578
* Fix: Added alias escaping in subquery by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5601
* fix: spark migrate:status does not show status with different namespaces by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5605
* BaseService - Use lowercase key in resetSingle by @najdanovicivan in https://github.com/codeigniter4/CodeIgniter4/pull/5596
* Fix `array_flatten_with_dots` ignores empty array values by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5606
* fix: debug toolbar Routes Params output by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5619
* fix: DownloadResponse memory leak by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5623
* fix: spark does not show Exception by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5638
* fix: Config CSRF $redirect does not work by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5665
* fix: do not call header() if headers have already been sent by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5680
* fix: $routes->setDefaultMethod() does not work by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5682
* fix: debug toolbar vars response headers includes request headers by @zl59503020 in https://github.com/codeigniter4/CodeIgniter4/pull/5701
* fix: 404 override controller does not output Response object body by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5703
* fix: auto routes incorrectly display route filters with GET method by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5712
* fix: Model::paginate() missing argument $group by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5699
* Fix options are not passed to Command $params by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5206
* fix: forceGlobalSecureRequests break URI schemes other than HTTP by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5730
* fix: TypeError when `$tokenRandomize = true` and no token posted by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5742
* fix: $builder->ignore()->insertBatch() only ignores on first iteration by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5672
* fix: app/Config/Routes.php is loaded twice on Windows by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5780
* fix: table name is double prefixed when LIKE clause by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5778
* fix: Publisher $restrictions regex to FCPATH by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5793
* fix: Timer::getElapsedTime() returns incorrect value by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5798
* bug: Publisher $restrictions regex typo by @MGatner in https://github.com/codeigniter4/CodeIgniter4/pull/5800
* fix: [Validation] valid_date ErrorException when the field is not sent by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5804
* fix: [Pager] can't get correct current page from segment by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5803
* fix: bug that allows dynamic controllers to be used by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5814
* config: remove App\ and Config\ in autoload.psr-4 in app starter composer.json by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5824
* fix: failover's DBPrefix not working by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5816
* fix: Validation returns incorrect errors after Redirect with Input by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5844
* feat: [Parser] add configs to change conditional delimiters by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5842
* fix: Commands::discoverCommands() loads incorrect classname by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5849
* fix: Publisher::discover() loads incorrect classname by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5858
* fix: validation errors in Model are not cleared when running validation again by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5861
* fix: Parser fails with `({variable})` in loop by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5840
* fix: [BaseConfig] string value is set from environment variable even if it should be int/float by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5779
* fix: add Escaper Exception classes in $coreClassmap by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5891
* fix: Composer PSR-4 overwrites Config\Autoload::$psr4 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5902
* fix: Reverse Routing does not take into account the default namespace by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5936
* fix:  [Validation] Fields with an asterisk throws exception by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5938
* fix: GDHandler::convert() does not work by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5969
* fix: Images\Handlers\GDHandler Implicit conversion from float to int loses precision by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5965
* fix: GDHandler::save() removes transparency by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5971
* fix: route limit to subdomains does not work by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5961
* fix: Model::_call() static analysis by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5970
* fix: invalid css in error_404.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5978
* Fix: Route placeholder (:any) with {locale} by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6003
* Changing the subquery builder for the Oracle by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5999
* fix: CURLRequest request body is not reset on the next request by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6014
* Bug: The SQLSRV driver ignores the port value from the config. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6036
* fix: `set_radio()` not working as expected by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6037
* fix: add config for SQLite3 Foreign Keys by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6050
* fix: Ignore non-HTML responses in storePreviousURL by @tearoom6 in https://github.com/codeigniter4/CodeIgniter4/pull/6012
* fix: SQLite3\Table::copyData() does not escape column names by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6055
* Fix `slash_item()` erroring when property fetched does not exist on `Config\App` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/6058

### New Features
* Feature Add Oracle driver by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/2487
* feat: new improved auto router by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5889
* feat: new improved auto router `spark routes` command by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5953
* feat: `db:table` command by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5979

### Enhancements
* feat: CSP enhancements by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5516
* Feature: Subqueries in the FROM section by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5510
* Added new View Decorators. by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/5567
* feat: auto routes listing by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5590
* Feature: "spark routes" command shows routes with closure. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5651
* feat: `spark routes` shows filters by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5628
* Allow calling getQuery() multiple times, and other improvements by @vlakoff in https://github.com/codeigniter4/CodeIgniter4/pull/5127
* feat: add Controller::validateData() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5639
* feat: can add route handler as callable by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5713
* Checking if the subquery uses the same object as the main query by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5743
* Feature: Subquery for SELECT by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5736
* Extend Validation from BaseConfig so Registrars can add rules. by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/5789
* config: add mime type for webp by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5838
* feat: add `$includeDir` option to `get_filenames()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5862
* feat: throws exception when controller name in routes contains `/` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5885
* [PHPStan] Prepare for PHPStan 1.6.x-dev by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/5876
* [Rector] Add back SimplifyUselessVariableRector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/5911
* Redirecting Routes. Placeholders. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5916
* script_tag(): cosmetic for value-less attributes by @xlii-chl in https://github.com/codeigniter4/CodeIgniter4/pull/5884
* feat: QueryBuilder raw SQL string support by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5817
* improve Router Exception message by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5984
* feat: DBForge::addField() `default` value raw SQL string support by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5957
* Add sample file for preloading by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5974
* Feature. QueryBuilder. Query union. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6015
* feat: `getFieldData()` returns nullable data on PostgreSQL by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5981

### Refactoring
* refactor: add Factories::models() to suppress PHPStan error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5358
* Fixed style for PHP7.4 by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5581
* Fix Autoloader::initialize() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5592
* refactor: CURLRequest and the slow tests by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5593
* Refactor `if_exist` validation with dot notation by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5607
* refactor: small changes in Filters and Router by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5627
* refactor: replace deprecated `getFilterForRoute()` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5624
* refactor: make BaseController abstract by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5647
* refactor: move logic to prevent access to initController by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5648
* refactor: remove migrations routes by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5652
* refactor: update Kint CSP nonce by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5657
* Deprecate object implementations of `clean_path()` function by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5681
* refactor: Session does not use cookies() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5656
* refactor: replace deprecated Response::getReason() with getReasonPhrase() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5700
* refactor: isCLI() in CLIRequest and IncomingRequest by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5653
* refactor: CodeIgniter has context by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5650
* Forge use statement by @mostafakhudair in https://github.com/codeigniter4/CodeIgniter4/pull/5729
* refactor: remove `&` before $db by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5726
* refactor: remove unneeded `&` references in ContentSecurityPolicy.php by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5734
* Nonce replacement optimization. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5733
* [Rector] Clean up skip config and re-run Rector by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/5813
* refactor: DB Session Handler by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5696
* Rename `Abstact` to `Abstract` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5833
* refactor: extract RedirectResponse::withErrors() method by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5860
* Optimizing the RouteCollection::getRoutes() method by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5918
* refactor: add strtolower() to Request::getMethod() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5963
* refactor: remove `$_SERVER['HTTP_HOST']` in RouteCollection by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5962
* refactor: deprecate const `EVENT_PRIORITY_*` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6000
* fix: replace EVENT_PRIORITY_NORMAL with Events::PRIORITY_NORMAL by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6005
* Router class optimization. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/6004
* Prefer `is_file()` by @MGatner in https://github.com/codeigniter4/CodeIgniter4/pull/6025
* refactor: use get_filenames() 4th param in FileLocator by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6026
* refactor: use get_filenames() 4th param by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6031
* refactor: CodeIgniter $context check by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6047
* Small change to improve code reading by @valmorflores in https://github.com/codeigniter4/CodeIgniter4/pull/6051
* refactor: remove `CodeIgniter\Services` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/6053

See [CHANGELOG_4.1.md](./changelogs/CHANGELOG_4.1.md)
