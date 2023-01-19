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

See [CHANGELOG_4.2.md](./changelogs/CHANGELOG_4.2.md)
