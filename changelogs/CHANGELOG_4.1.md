# Changelog 4.1

## [v4.1.9](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.9) (2022-02-25)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.8...v4.1.9)

**SECURITY**

* *Remote CLI Command Execution Vulnerability* was fixed. See the [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-xjp4-6w75-qrj7) for more information.
* *Cross-Site Request Forgery (CSRF) Protection Bypass Vulnerability* was fixed. See the [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-4v37-24gm-h554) for more information.

## [v4.1.8](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.8) (2022-01-24)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.7...v4.1.8)

**SECURITY**

* *XSS Vulnerability* in the `API\ResponseTrait` was fixed. See the [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-7528-7jg5-6g62) for more information.

## [v4.1.7](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.7) (2022-01-09)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.6...v4.1.7)

**Breaking Changes**

* fix: replace deprecated FILTER_SANITIZE_STRING by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5555

**Fixed Bugs**

* fix: BaseConnection::getConnectDuration() number_format(): Passing null to parameter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5536
* Fix: Debug toolbar selectors by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5544
* Fix: Toolbar. ciDebugBar.showTab() context. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5554
* Refactor Database Collector display by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5553

## [v4.1.6](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.6) (2022-01-03)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.5...v4.1.6)

**SECURITY**

* *Deserialization of Untrusted Data* found in the ``old()`` function was fixed. See the [Security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-w6jr-wj64-mc9x) for more information.

**Breaking Changes**

* fix: Incorrect type `BaseBuilder::$tableName` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5378
* fix: Validation cannot handle array item by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5405

**Fixed Bugs**

* fix: FileLocator cannot find files in sub-namespaces of the same vendor by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5292
* fix: add a workaround for upgraded users who do not update Config\Exceptions by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5314
* Fix db escape negative integers by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5277
* Fix: remove incorrect processing of CLI params by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5274
* fix: table alias is prefixed when LIKE clause by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5361
* fix: `dot_array_search()` unexpected array structure causes Type Error by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5372
* fix: UploadedFile::move() may return incorrect value by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5302
* fix: BaseModel::insert() may not pass all the values from Entity by @katie1348 in https://github.com/codeigniter4/CodeIgniter4/pull/4980
* fix: `IncomingRequest::getJsonVar()` may cause TypeError by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5392
* chore: fix example test code for appstarter and module by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5421
* fix: Model::save() may call unneeded countAllResults() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5439
* fix: errors when MariaDB/MySQL has `ANSI_QUOTES` enabled by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5425
* fix: Security class sends cookies immediately by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5429
* fix: `is_cli()` returns `true` when `$_SERVER['HTTP_USER_AGENT']` is missing by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5393
* fix: `MySQLi\Connection::_foreignKeyData()` may return duplicated rows by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5416
* fix: `number_to_currency()` error on PHP 8.1 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5454
* fix: VENDORPATH definition by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5453
* fix: Throttler does not show correct token time by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5470
* fix: directory_mirror() throws an error if destination directory exists by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5493
* fix: KINT visual error when activating CSP by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5501

**New Features**

* feat: add filter to check invalid chars in user input by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5227

**Enhancements**

* Add support for PHP 8.1 by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/4883
* Toolbar - Make it possible to turn off var data collection by @najdanovicivan in https://github.com/codeigniter4/CodeIgniter4/pull/5295
* feat: add CSRF token randomization by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5283
* Display file:line and trace information to database queries in debug toolbar by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/5334
* feat: add SecureHeaders filter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5419
* Feature: BaseBuilder instance as subquery. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5488

**Refactoring**

* Do not inappropriately register bind when the value is a closure by @vlakoff in https://github.com/codeigniter4/CodeIgniter4/pull/5247
* refactor: replace $request->uri with $request->getUri() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5346
* Determine if binds are simple or named by looking at the $binds array by @vlakoff in https://github.com/codeigniter4/CodeIgniter4/pull/5138
* Remove unneeded cast to array by @vlakoff in https://github.com/codeigniter4/CodeIgniter4/pull/5379
* Additional fix for deprecated `null` usage by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5388
* refactor: dot_array_search() regex by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5443
* refactor: Time::getDst() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5415
* The View class. Optimizing duplicate code. by @iRedds in https://github.com/codeigniter4/CodeIgniter4/pull/5455
* refactor: fix `ThrottleTest::testFlooding` by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5463
* refactor: update deprecated method in DatetimeCast by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5474
* Remove semicolons from SQL statements. by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5513

**New Contributors**

* @katie1348 made their first contribution in https://github.com/codeigniter4/CodeIgniter4/pull/4980

## [v4.1.5](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.5) (2021-11-08)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.4...v4.1.5)

**Fixed bugs:**

* Fix entity name generation when bundled in model by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5055
* Fix `Model::__call` throwing `BadMethodCallException` on empty results by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5139
* Fixed an issue where the dropForeginKey method would execute an empty query when the dropConstraintStr property was empty. by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5173
* Update 'updated_at' when enabled in replace() by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/4684
* Fix query binding with two colons in query by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5117
* Fixed the problem that _createTable does not take into account that it returns true. by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5133
* Fixed a problem with not run escape for identities in like when `insensitiveSearch` is true. by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5170
* Fixed an issue where an unnecessary prefix was given when the random number was a column. by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5179
* Always escape identifiers in the set(), setUpdateBatch(), and insertBatch() by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5132
* Error when value is an object - validating api data by @daycry in https://github.com/codeigniter4/CodeIgniter4/pull/5142
* Fix color not updated in several places of the precompiled CSS by @vlakoff in https://github.com/codeigniter4/CodeIgniter4/pull/5155
* Fix debugbar styles printing by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5100
* Fix highlighting in database debug toolbar by @vlakoff in https://github.com/codeigniter4/CodeIgniter4/pull/5129
* Fix debug toolbar db connection count by @danielTiringer in https://github.com/codeigniter4/CodeIgniter4/pull/5172
* Fix CSRF filter does not work when set it to only post by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5194
* Add CSRF Protection for PUT/PATCH/DELETE by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5228
* Fix GC issue when session lifetime is set to 0 by @lf-uraku-yuki in https://github.com/codeigniter4/CodeIgniter4/pull/4744
* Fix wrong helper path resolution by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5246
* Fix: remove CURLRequest headers sharing from $_SERVER by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5249
* Fix Localization not working/being ignored for 404 page by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5267
* fix: module filters are not discovered when using route filters by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5280
* IncomingRequest - Trim trailing slash by @najdanovicivan in https://github.com/codeigniter4/CodeIgniter4/pull/4974
* Previous Responses by @MGatner in https://github.com/codeigniter4/CodeIgniter4/pull/5034
* (Paging) Ensure page validity by @puschie286 in https://github.com/codeigniter4/CodeIgniter4/pull/5125
* Fix variable variable `$$id` in RedisHandler by @Terrorboy in https://github.com/codeigniter4/CodeIgniter4/pull/5062
* Fixes and enhancements to Exceptions by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5052

**Implemented enhancements:**

* feat: `_` can be used as separators in environment variable names by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5156
* Multiple filters for a route and classname filter by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5128
* Feature - Mark duplicate queries by @danielTiringer in https://github.com/codeigniter4/CodeIgniter4/pull/5185
* [Debug] Add formatted query string to timeline. by @sfadschm in https://github.com/codeigniter4/CodeIgniter4/pull/5196
* [Debug] Improve keyword highlighting and escaping of query strings.  by @sfadschm in https://github.com/codeigniter4/CodeIgniter4/pull/5200
* Add `dropKey` method to `Forge` by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5171
* Reduce memory usage of insertBatch(), updateBatch() by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5202
* Add Session based CSRF Protection by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5201
* feat: add valid_url_strict rule by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5268

**Merged pull requests:**

* Merge branch '4.2' by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5060
* Update to latest laminas-escaper 2.9.0 by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/5065
* Remove unintended dead code in pre-commit by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5116
* Adjust orange color in debug toolbar by @vlakoff in https://github.com/codeigniter4/CodeIgniter4/pull/5136
* Extract method to get prefix for DB access function by @ytetsuro in https://github.com/codeigniter4/CodeIgniter4/pull/5178
* Improve `model()` auto-completion by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5186
* Rename toolbar loader to be a regular JS file by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5224
* [HTTP] Update Http Status Description based on latest iana.org by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/5235
* Remove CSRF properties by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5231
* Remove static variables for PHP 8.1 by @kenjis in https://github.com/codeigniter4/CodeIgniter4/pull/5262
* Replace usage of `FILTER_SANITIZE_STRING` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5263
* Simplify logic of `number_to_roman` function by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5270
* Fix compatibility of `PgSql\Result` on closing the result instance by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5279
* Fix compatibility of Postgres result for PHP 8.1 by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5278

**New Contributors**

* @Terrorboy made their first contribution in https://github.com/codeigniter4/CodeIgniter4/pull/5062
* @vlakoff made their first contribution in https://github.com/codeigniter4/CodeIgniter4/pull/5136
* @Felipebros made their first contribution in https://github.com/codeigniter4/CodeIgniter4/pull/5152
* @daycry made their first contribution in https://github.com/codeigniter4/CodeIgniter4/pull/5142
* @danielTiringer made their first contribution in https://github.com/codeigniter4/CodeIgniter4/pull/5172

## [v4.1.4](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.4) (2021-09-06)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.3...v4.1.4)

This release focuses on code style. All changes (except those noted below) are cosmetic to bring the code in line with the new
[CodeIgniter Coding Standard](https://github.com/CodeIgniter/coding-standard) (based on PSR-12).

**What's Changed**

* Use php-cs-fixer as coding style tool by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/4770
* Remove unused local variables by @jeromegamez in https://github.com/codeigniter4/CodeIgniter4/pull/4783
* Use static lambda if a binding to `$this` is not required. by @jeromegamez in https://github.com/codeigniter4/CodeIgniter4/pull/4784
* Use/Fix `preg_quote()` delimiters by @jeromegamez in https://github.com/codeigniter4/CodeIgniter4/pull/4789
* Don't override `$path` parameter by @jeromegamez in https://github.com/codeigniter4/CodeIgniter4/pull/4787
* Don't override `$value` parameter by @jeromegamez in https://github.com/codeigniter4/CodeIgniter4/pull/4788
* Add brackets to clarify intent and avoid unwanted side-effects by @jeromegamez in https://github.com/codeigniter4/CodeIgniter4/pull/4791
* Remove removed `safe_mode` ini Option by @jeromegamez in https://github.com/codeigniter4/CodeIgniter4/pull/4795
* It will fix undefined index cid error when sending emails with embedded images by @mmfarhan in https://github.com/codeigniter4/CodeIgniter4/pull/4798
* Revert Model coalesce by @MGatner in https://github.com/codeigniter4/CodeIgniter4/pull/4819
* Master language constructs shall be used instead of aliases. by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/4847
* [Commands] Remove unused $minPHPVersion property at Serve command by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/4852
* Update to latest laminas-escaper ^2.8 by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/4878
* Remove 'memory_usage' from 'displayPerformanceMetrics()' comment by @Mauricevb in https://github.com/codeigniter4/CodeIgniter4/pull/4939
* Remove useless code separator comments by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/4946
* Optimize Filters by @mostafakhudair in https://github.com/codeigniter4/CodeIgniter4/pull/4965
* Fix properly the phpstan error in 0.12.93 by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/4970
* Manual cleanup of docblocks and comments by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/4964
* Make Cookie compatible with ArrayAccess by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5004
* Replace deprecated FILTER_SANITIZE_STRING by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5005
* Make CookieStore compatible with IteratorAggregate::getIterator by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5010
* Make the session handlers all compatible with SessionHandlerInterface by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5012
* Make CITestStreamFilter compatible with php_user_filter by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5014
* Make Time compatible with DateTime by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5022
* Add `ReturnTypeWillChange` attribute to Entity by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5028
* Replace unused Entity private method by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5029
* Make File compatible with SplFileInfo by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5040
* Update documentation code samples by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5039
* PHP Copy-Paste Detector by @MGatner in https://github.com/codeigniter4/CodeIgniter4/pull/5031
* Fix key casting in form_dropdown helper. by @sfadschm in https://github.com/codeigniter4/CodeIgniter4/pull/5035
* Switch to official coding standard by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/5038

**New Contributors**

* @mmfarhan made their first contribution in https://github.com/codeigniter4/CodeIgniter4/pull/4798
* @Mauricevb made their first contribution in https://github.com/codeigniter4/CodeIgniter4/pull/4939

## [v4.1.3](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.3) (2021-06-06)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.2...v4.1.3)

**Fixed bugs:**

- Bug: Error using SQLITE3 strftime in CodeIgniter 4.1.2 [\#4760](https://github.com/codeigniter4/CodeIgniter4/issues/4760)
- Bug: Caching something through cron, is not accessible in the web application [\#4751](https://github.com/codeigniter4/CodeIgniter4/issues/4751)
- Bug: SQLite Drop Column [\#4746](https://github.com/codeigniter4/CodeIgniter4/issues/4746)
- Bug: CURL Class - BaseURI options notworking  [\#4713](https://github.com/codeigniter4/CodeIgniter4/issues/4713)
- Bug: autorouting [\#4711](https://github.com/codeigniter4/CodeIgniter4/issues/4711)
- Bug: curlrequest not using baseURI on localhost [\#4707](https://github.com/codeigniter4/CodeIgniter4/issues/4707)
- Bug: cli not working with cron [\#4699](https://github.com/codeigniter4/CodeIgniter4/issues/4699)

**Closed issues:**

- Bug: Class 'Locale' not found [\#4775](https://github.com/codeigniter4/CodeIgniter4/issues/4775)
- Bug: deprecated notice on CodeIgniter\HTTP\RequestInterface::getMethod\(\) [\#4717](https://github.com/codeigniter4/CodeIgniter4/issues/4717)
- Allow to join models between primary keys and foreign keys [\#4714](https://github.com/codeigniter4/CodeIgniter4/issues/4714)
- DateTime::\_\_construct\(\): Failed to parse time string \(\) at position 0 \(�\): Unexpected character [\#4708](https://github.com/codeigniter4/CodeIgniter4/issues/4708)
- Bug: Query Builder breaks with SQL function LENGTH\(\) and column name "row" [\#4687](https://github.com/codeigniter4/CodeIgniter4/issues/4687)

**Merged pull requests:**

- Expand Query named binds recognition [\#4769](https://github.com/codeigniter4/CodeIgniter4/pull/4769) ([paulbalandan](https://github.com/paulbalandan))
- \[Rector\] Remove @var from class constant [\#4766](https://github.com/codeigniter4/CodeIgniter4/pull/4766) ([samsonasik](https://github.com/samsonasik))
- Set WarningsReturnAsErrors = 0 before connection [\#4762](https://github.com/codeigniter4/CodeIgniter4/pull/4762) ([obelisk-services](https://github.com/obelisk-services))
- \[Rector\] Apply Rector: VarConstantCommentRector [\#4759](https://github.com/codeigniter4/CodeIgniter4/pull/4759) ([samsonasik](https://github.com/samsonasik))
- \[Autoloader\] include\_once is not needed on Autoloader::loadClass\(\) with no namespace [\#4756](https://github.com/codeigniter4/CodeIgniter4/pull/4756) ([samsonasik](https://github.com/samsonasik))
- Fix imagemagick build [\#4755](https://github.com/codeigniter4/CodeIgniter4/pull/4755) ([michalsn](https://github.com/michalsn))
- \[Rector\] Apply Rector: MoveVariableDeclarationNearReferenceRector [\#4752](https://github.com/codeigniter4/CodeIgniter4/pull/4752) ([samsonasik](https://github.com/samsonasik))
- SQLite3 "nullable" [\#4749](https://github.com/codeigniter4/CodeIgniter4/pull/4749) ([MGatner](https://github.com/MGatner))
- Remove $response variable at ControllerResponse::\_\_construct\(\) as never defined [\#4747](https://github.com/codeigniter4/CodeIgniter4/pull/4747) ([samsonasik](https://github.com/samsonasik))
- Use variable for Config/Paths config to reduce repetitive definition [\#4745](https://github.com/codeigniter4/CodeIgniter4/pull/4745) ([samsonasik](https://github.com/samsonasik))
- \[Rector\] Apply Rector : ListToArrayDestructRector [\#4743](https://github.com/codeigniter4/CodeIgniter4/pull/4743) ([samsonasik](https://github.com/samsonasik))
- Add default TTL [\#4742](https://github.com/codeigniter4/CodeIgniter4/pull/4742) ([MGatner](https://github.com/MGatner))
- update return sample of `dot array\_search\(\)` [\#4740](https://github.com/codeigniter4/CodeIgniter4/pull/4740) ([totoprayogo1916](https://github.com/totoprayogo1916))
- Additional check for `$argv` variable when detecting CLI [\#4739](https://github.com/codeigniter4/CodeIgniter4/pull/4739) ([paulbalandan](https://github.com/paulbalandan))
- Ensure variable declarations [\#4737](https://github.com/codeigniter4/CodeIgniter4/pull/4737) ([jeromegamez](https://github.com/jeromegamez))
- Fix setting of value in Cookie's flag attributes [\#4736](https://github.com/codeigniter4/CodeIgniter4/pull/4736) ([paulbalandan](https://github.com/paulbalandan))
- Add missing imports [\#4735](https://github.com/codeigniter4/CodeIgniter4/pull/4735) ([jeromegamez](https://github.com/jeromegamez))
- Add environment spark command [\#4734](https://github.com/codeigniter4/CodeIgniter4/pull/4734) ([paulbalandan](https://github.com/paulbalandan))
- Remove explicit condition that is always true [\#4731](https://github.com/codeigniter4/CodeIgniter4/pull/4731) ([jeromegamez](https://github.com/jeromegamez))
- Deduplicate code [\#4730](https://github.com/codeigniter4/CodeIgniter4/pull/4730) ([jeromegamez](https://github.com/jeromegamez))
- Replace `isset\(\)` with the `??` null coalesce operator [\#4729](https://github.com/codeigniter4/CodeIgniter4/pull/4729) ([jeromegamez](https://github.com/jeromegamez))
- Remove unused imports [\#4728](https://github.com/codeigniter4/CodeIgniter4/pull/4728) ([jeromegamez](https://github.com/jeromegamez))
- Fix truncated SCRIPT\_NAME [\#4726](https://github.com/codeigniter4/CodeIgniter4/pull/4726) ([MGatner](https://github.com/MGatner))
- Expand CLI detection [\#4725](https://github.com/codeigniter4/CodeIgniter4/pull/4725) ([paulbalandan](https://github.com/paulbalandan))
- \[Rector\] Add custom Rector Rule: RemoveErrorSuppressInTryCatchStmtsRector rector rule [\#4724](https://github.com/codeigniter4/CodeIgniter4/pull/4724) ([samsonasik](https://github.com/samsonasik))
- Test with MySQL 8 [\#4721](https://github.com/codeigniter4/CodeIgniter4/pull/4721) ([jeromegamez](https://github.com/jeromegamez))
- Replace URI string casts [\#4716](https://github.com/codeigniter4/CodeIgniter4/pull/4716) ([MGatner](https://github.com/MGatner))
- Format URI directly [\#4715](https://github.com/codeigniter4/CodeIgniter4/pull/4715) ([MGatner](https://github.com/MGatner))
- Additional File functions [\#4712](https://github.com/codeigniter4/CodeIgniter4/pull/4712) ([MGatner](https://github.com/MGatner))
- Remove unused private rowOffset property in Database/SQLSRV/Result.php [\#4709](https://github.com/codeigniter4/CodeIgniter4/pull/4709) ([samsonasik](https://github.com/samsonasik))
- Check for configured instead of hard-coded database in DbUtilsTest [\#4705](https://github.com/codeigniter4/CodeIgniter4/pull/4705) ([jeromegamez](https://github.com/jeromegamez))
- Revert UG margins [\#4704](https://github.com/codeigniter4/CodeIgniter4/pull/4704) ([MGatner](https://github.com/MGatner))
- Create .git/hooks directory if not already present [\#4703](https://github.com/codeigniter4/CodeIgniter4/pull/4703) ([jeromegamez](https://github.com/jeromegamez))
- Annotate specifically designed slow tests with custom limits [\#4698](https://github.com/codeigniter4/CodeIgniter4/pull/4698) ([paulbalandan](https://github.com/paulbalandan))
- Cache robustness [\#4697](https://github.com/codeigniter4/CodeIgniter4/pull/4697) ([MGatner](https://github.com/MGatner))

## [v4.1.2](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.2) (2021-05-18)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.1...v4.1.2)

**Implemented enhancements:**

- New HTTP classes, ``Cookie`` and ``CookieStore``, for abstracting web cookies.
- New ``assertRedirectTo()`` assertion available for HTTP tests.
- New logger handler, ``ErrorlogHandler``, that writes to ``error_log()``.
- Entity. Added custom type casting functionality.
- New option in routing. The ``priority`` option lower the priority of specific route processing.
- The ``Autoloader`` class can now load files which do not contain PHP classes. The list of `non-class` files will be listed in the ``$files`` property of ``Config\Autoload`` class.

**Deprecations:**

- Deprecated ``Codeigniter\View\View::$currentSection`` property.
- Language strings and exceptions on invalid cookie samesite are deprecated for the ``CookieException``'s own exception message.
- Deprecated `CodeIgniter\Entity` in favor of `CodeIgniter\Entity\Entity`
- Deprecated cookie-related properties of ``Response`` in order to use the ``Cookie`` class.
- Deprecated cookie-related properties of ``Security`` in order to use the ``Cookie`` class.
- Deprecated cookie-related properties of ``Session`` in order to use the ``Cookie`` class.
- Deprecated ``Security::isExpired()`` to use the ``Cookie``'s internal expires status.
- Deprecated ``CIDatabaseTestCase`` to use the ``DatabaseTestTrait`` instead.
- Deprecated ``FeatureTestCase`` to use the ``FeatureTestTrait`` instead.
- Deprecated ``ControllerTester`` to use the ``ControllerTestTrait`` instead.
- Consolidated and deprecated ``ControllerResponse`` and ``FeatureResponse`` in favor of ``TestResponse``.
- Deprecated ``Time::instance()``, use ``Time::createFromInstance()`` instead (now accepts ``DateTimeInterface``).
- Deprecated ``IncomingRequest::removeRelativeDirectory()``, use ``URI::removeDotSegments()`` instead
- Deprecated ``\API\ResponseTrait::failValidationError`` to use ``\API\ResponseTrait::failValidationErrors`` instead

**Fixed bugs:**

- Bug: NULL Fields definition not working for MSQLi Forge in Migration due property $\_null and $null names difference [\#4693](https://github.com/codeigniter4/CodeIgniter4/issues/4693)
- Bug: Missing Cookie Config [\#4619](https://github.com/codeigniter4/CodeIgniter4/issues/4619)
- Bug:  [\#4610](https://github.com/codeigniter4/CodeIgniter4/issues/4610)
- Bug: Customized Validation language does not take effect [\#4597](https://github.com/codeigniter4/CodeIgniter4/issues/4597)
- Bug: colon issue in query binding [\#4595](https://github.com/codeigniter4/CodeIgniter4/issues/4595)
- Bug: set\_checkbox\(\) default value not working  [\#4582](https://github.com/codeigniter4/CodeIgniter4/issues/4582)
- Bug: Request  & Response objects   stored multiple times [\#4580](https://github.com/codeigniter4/CodeIgniter4/issues/4580)
- Bug: Class information on output is missing during migrate:rollback command [\#4579](https://github.com/codeigniter4/CodeIgniter4/issues/4579)
- Bug: Cookie path replaced with system's PATH env variable [\#4559](https://github.com/codeigniter4/CodeIgniter4/issues/4559)
- Bug: Validation::withRequest\(\) method does not receive data. [\#4552](https://github.com/codeigniter4/CodeIgniter4/issues/4552)
- `esc` and `nl2br` combo gives nasty HTML error output [\#4533](https://github.com/codeigniter4/CodeIgniter4/issues/4533)
- Bug: typo error when creating a model using php spark make:model [\#4525](https://github.com/codeigniter4/CodeIgniter4/issues/4525)
- Bug: if\_exist not working with ".\*" notation [\#4521](https://github.com/codeigniter4/CodeIgniter4/issues/4521)
- Bug: Query::matchSimpleBinds index problem only toolbar. [\#4518](https://github.com/codeigniter4/CodeIgniter4/issues/4518)
- Bug: Unable to use debugger toolbar on a live server [\#4516](https://github.com/codeigniter4/CodeIgniter4/issues/4516)
- Missing config options and config options repetition [\#4504](https://github.com/codeigniter4/CodeIgniter4/issues/4504)
- Bug: db:create command should create database even database not exists yet, and defined in .env [\#4498](https://github.com/codeigniter4/CodeIgniter4/issues/4498)
- Bug: Differences in file names created with  CLI command [\#4495](https://github.com/codeigniter4/CodeIgniter4/issues/4495)
- Bug: Session removeTempdata\(\) method not accepting arrays. [\#4490](https://github.com/codeigniter4/CodeIgniter4/issues/4490)
- Bug: Session remove\(\) method not removing tempdata sessions. [\#4489](https://github.com/codeigniter4/CodeIgniter4/issues/4489)
- Bug: Session getFlashdata\(\) not support for dot notation. [\#4488](https://github.com/codeigniter4/CodeIgniter4/issues/4488)
- Bug: New Service replacement fails at service provider precedence on core factory implementations [\#4483](https://github.com/codeigniter4/CodeIgniter4/issues/4483)
- Bug: Filter is not work ! [\#4482](https://github.com/codeigniter4/CodeIgniter4/issues/4482)
- Bug: PHPStorm anlysis fault [\#4474](https://github.com/codeigniter4/CodeIgniter4/issues/4474)
- Bug: apache mod\_userdir causes weird URL segment duplication [\#4471](https://github.com/codeigniter4/CodeIgniter4/issues/4471)
- Postgre Forge doesn't use schema in creating tables [\#4469](https://github.com/codeigniter4/CodeIgniter4/issues/4469)
- Bug: UG QueryBuilder::from\(\) wrong SQL example. [\#4464](https://github.com/codeigniter4/CodeIgniter4/issues/4464)
- Bug: results on getX\(\) not equal  [\#4452](https://github.com/codeigniter4/CodeIgniter4/issues/4452)
- Bug: Queries with LOWER\( throwing errors  [\#4443](https://github.com/codeigniter4/CodeIgniter4/issues/4443)
- Bug: RouteCollection::getHTTPVerb\(\) can return null [\#4435](https://github.com/codeigniter4/CodeIgniter4/issues/4435)
- Bug: can't run `spark migrate` on CI server [\#4428](https://github.com/codeigniter4/CodeIgniter4/issues/4428)
- Bug: URI Routing Placeholders  [\#4421](https://github.com/codeigniter4/CodeIgniter4/issues/4421)
- Bug: Third Flags needs default [\#4411](https://github.com/codeigniter4/CodeIgniter4/issues/4411)
- Bug: another Flags needs default [\#4410](https://github.com/codeigniter4/CodeIgniter4/issues/4410)
- Bug: Flags needs default value [\#4409](https://github.com/codeigniter4/CodeIgniter4/issues/4409)
- Bug: log\_message passed object [\#4407](https://github.com/codeigniter4/CodeIgniter4/issues/4407)
- Bug: Model creation error  [\#4393](https://github.com/codeigniter4/CodeIgniter4/issues/4393)
- Bug: If the file name contains "app", "php spark make: migration" will not create it successfully. [\#4383](https://github.com/codeigniter4/CodeIgniter4/issues/4383)
- Bug: IncomingRequest.php getVar\(\)  [\#4381](https://github.com/codeigniter4/CodeIgniter4/issues/4381)
- Bug: Minimum PHP Version Discrepancy [\#4361](https://github.com/codeigniter4/CodeIgniter4/issues/4361)
- Bug: insertBatch generates an incorrect SQL query if the fields differ only in number at the end [\#4345](https://github.com/codeigniter4/CodeIgniter4/issues/4345)
- Bug: Database/Live tests fail [\#4336](https://github.com/codeigniter4/CodeIgniter4/issues/4336)
- Bug: red line on model by setPrefix & prefixTable [\#4329](https://github.com/codeigniter4/CodeIgniter4/issues/4329)
- Bug: $model-\>errors\(\) produce output when no error [\#4323](https://github.com/codeigniter4/CodeIgniter4/issues/4323)
- Bug: Can't Rewrite System Validation Messages [\#4318](https://github.com/codeigniter4/CodeIgniter4/issues/4318)
- Bug: "useSoftDelete" for model files generated by `phpspark` [\#4316](https://github.com/codeigniter4/CodeIgniter4/issues/4316)
- Bug: require the unused namespace [\#4309](https://github.com/codeigniter4/CodeIgniter4/issues/4309)
- Bug: FeatureTest cannot assert Status\(404\) [\#4306](https://github.com/codeigniter4/CodeIgniter4/issues/4306)
- Bug: BaseBuilder-\>\_insert [\#4302](https://github.com/codeigniter4/CodeIgniter4/issues/4302)
- Bug: previous\_url\(\) contains current URL after reloading a page. [\#4299](https://github.com/codeigniter4/CodeIgniter4/issues/4299)
- Bug: Cannot add route to controller in filename with dash/hyphen [\#4294](https://github.com/codeigniter4/CodeIgniter4/issues/4294)
- Bug: FeatureTest dies when throws RedirectException/cached page [\#4288](https://github.com/codeigniter4/CodeIgniter4/issues/4288)
- Bug: /test.php show home page [\#4263](https://github.com/codeigniter4/CodeIgniter4/issues/4263)
- Bug: Fabricator::fake\(\) function is breaking when it returns an array [\#4261](https://github.com/codeigniter4/CodeIgniter4/issues/4261)
- Bug: Session issue with CI Environment set to Testing \(CI4\) [\#4248](https://github.com/codeigniter4/CodeIgniter4/issues/4248)
- Bug: Wrong HTML code in output of "form\_input" helper function [\#4235](https://github.com/codeigniter4/CodeIgniter4/issues/4235)
- make:scaffold input information is missing  [\#4230](https://github.com/codeigniter4/CodeIgniter4/issues/4230)
- Bug: CodeIgniter 4.1.1 - csrf token is always regenerated [\#4224](https://github.com/codeigniter4/CodeIgniter4/issues/4224)
- Bug: getFileMultiple expects an "0" index but string is given [\#4221](https://github.com/codeigniter4/CodeIgniter4/issues/4221)
- Bug: cannot resolve Services::xxx\(\) [\#4220](https://github.com/codeigniter4/CodeIgniter4/issues/4220)
- Bug: tfoot\_open / tfoot\_close have no default when using custom table template [\#4219](https://github.com/codeigniter4/CodeIgniter4/issues/4219)
- Bug: Spark PHP version [\#4213](https://github.com/codeigniter4/CodeIgniter4/issues/4213)
- Bug: Soft deletes and model validation when unique [\#4162](https://github.com/codeigniter4/CodeIgniter4/issues/4162)
- Bug: Debug Toolbar - Memory Leak - Allocation Exception [\#4137](https://github.com/codeigniter4/CodeIgniter4/issues/4137)
- current\_url\(\) global method returning URLs without the index.php part.  [\#4116](https://github.com/codeigniter4/CodeIgniter4/issues/4116)
- Bug: appstarter HealthTest::testBaseUrlHasBeenSet fails [\#3977](https://github.com/codeigniter4/CodeIgniter4/issues/3977)
- Bug: Time::createFromTimestamp\(\) uses default timezone, not UTC for timestamp [\#3951](https://github.com/codeigniter4/CodeIgniter4/issues/3951)
- Bug: Unexpected filter behavior [\#3874](https://github.com/codeigniter4/CodeIgniter4/issues/3874)
- Bug: Double initializing of class [\#3855](https://github.com/codeigniter4/CodeIgniter4/issues/3855)
- Bug: Registrars take priority over .env [\#3845](https://github.com/codeigniter4/CodeIgniter4/issues/3845)
- Bug: SQLite3 NOT NULL prevents inserts [\#3599](https://github.com/codeigniter4/CodeIgniter4/issues/3599)
- Bug: Model doesn't reset errors in FeatureTestCase [\#3578](https://github.com/codeigniter4/CodeIgniter4/issues/3578)
- Bug: Problem in "/system/Database/Query.php" function "compileBinds\(\)" [\#3566](https://github.com/codeigniter4/CodeIgniter4/issues/3566)
- Bug: Exceptions cause risky Feature Tests [\#3114](https://github.com/codeigniter4/CodeIgniter4/issues/3114)
- Bug: current\_url\(\) loses subdomain [\#3004](https://github.com/codeigniter4/CodeIgniter4/issues/3004)

**Closed issues:**

- mysqli\_sql\_exception \#2002 [\#4640](https://github.com/codeigniter4/CodeIgniter4/issues/4640)
- intl - Name missing exception   [\#4636](https://github.com/codeigniter4/CodeIgniter4/issues/4636)
- HUGE BUG: update\(\) function updates all records if id is empty [\#4617](https://github.com/codeigniter4/CodeIgniter4/issues/4617)
- Bug: Validation rule "matches" doesn't work [\#4615](https://github.com/codeigniter4/CodeIgniter4/issues/4615)
- Bug:   chmod 777 writable/cache fixed codeignitor install for me [\#4598](https://github.com/codeigniter4/CodeIgniter4/issues/4598)
- Model-\>where method does not exist [\#4583](https://github.com/codeigniter4/CodeIgniter4/issues/4583)
- Transactions between two databases [\#4578](https://github.com/codeigniter4/CodeIgniter4/issues/4578)
- Bug: Mysql connection issue with MYSQLI\_CLIENT\_SSL\_DONT\_VERIFY\_SERVER\_CERT  [\#4558](https://github.com/codeigniter4/CodeIgniter4/issues/4558)
- Release cycle  [\#4526](https://github.com/codeigniter4/CodeIgniter4/issues/4526)
- Call to a member function setContentType\(\) on null - Responsetrait [\#4524](https://github.com/codeigniter4/CodeIgniter4/issues/4524)
- Bug: mock single\_service [\#4515](https://github.com/codeigniter4/CodeIgniter4/issues/4515)
- Bug: failed to open stream [\#4514](https://github.com/codeigniter4/CodeIgniter4/issues/4514)
- Array Validation Fails [\#4510](https://github.com/codeigniter4/CodeIgniter4/issues/4510)
- Bug: return $this-\>failValidationError\($validation-\>getErrors\(\)\) Has Invalid Signature [\#4506](https://github.com/codeigniter4/CodeIgniter4/issues/4506)
- Bug: Ok The Model ERRORS came right back with the newest build again! [\#4491](https://github.com/codeigniter4/CodeIgniter4/issues/4491)
- Bug: Composer install loads require-dev when I require another package [\#4477](https://github.com/codeigniter4/CodeIgniter4/issues/4477)
- Logger [\#4460](https://github.com/codeigniter4/CodeIgniter4/issues/4460)
- Bug: Improve creation of scaffolds with the CLI [\#4441](https://github.com/codeigniter4/CodeIgniter4/issues/4441)
- Request: Feature Test Optimization [\#4438](https://github.com/codeigniter4/CodeIgniter4/issues/4438)
- request.getVar not populated with GET parameters | unexpected behavior  [\#4418](https://github.com/codeigniter4/CodeIgniter4/issues/4418)
- Running via CLI - Only Default Controller works [\#4415](https://github.com/codeigniter4/CodeIgniter4/issues/4415)
- Parser content typehint \[strict\_types=1\] [\#4412](https://github.com/codeigniter4/CodeIgniter4/issues/4412)
- Toolbar::setFiles\(\) requires int \[strict\_types=1\] [\#4408](https://github.com/codeigniter4/CodeIgniter4/issues/4408)
- FeatureTest currently supports file testing?  [\#4405](https://github.com/codeigniter4/CodeIgniter4/issues/4405)
- Bug: set404Override now working in group rotes [\#4400](https://github.com/codeigniter4/CodeIgniter4/issues/4400)
- Dynamic URL [\#4394](https://github.com/codeigniter4/CodeIgniter4/issues/4394)
- ErrorException preg\_replace\_callback\(\): Unknown modifier '{' SYSTEMPATH/View/Parser.php at line 584 [\#4367](https://github.com/codeigniter4/CodeIgniter4/issues/4367)
- Feature: In HTTP Feature Testing, delivering in body in application/json format [\#4362](https://github.com/codeigniter4/CodeIgniter4/issues/4362)
- \[Dev\] Database Live Tests should depart from using deprecated CIDatabaseTestCase [\#4351](https://github.com/codeigniter4/CodeIgniter4/issues/4351)
- Bug: Migration in module \(different namespace\) do not find migrations [\#4348](https://github.com/codeigniter4/CodeIgniter4/issues/4348)
- Bug: getVar does not look at $\_SESSION as documentation suggests [\#4284](https://github.com/codeigniter4/CodeIgniter4/issues/4284)
- QBSelect, QBFrom, other properties cannot be accessed, modified from the model. [\#4255](https://github.com/codeigniter4/CodeIgniter4/issues/4255)
- Dev: Restrictions on trait "ResponseTrait" [\#4238](https://github.com/codeigniter4/CodeIgniter4/issues/4238)
- ResponseTrait trait \> Can the description support array? [\#4237](https://github.com/codeigniter4/CodeIgniter4/issues/4237)
- Feature: add old data in afterUpdate model event [\#4234](https://github.com/codeigniter4/CodeIgniter4/issues/4234)
- Dev:  [\#4233](https://github.com/codeigniter4/CodeIgniter4/issues/4233)
- Cache unable to write to /var/www/html/ci4test/writable/cache/  [\#4227](https://github.com/codeigniter4/CodeIgniter4/issues/4227)
- Documentation: multiple databases setup in the ENV also need to be setup in the database config file [\#4218](https://github.com/codeigniter4/CodeIgniter4/issues/4218)
- Documentation: $this-\>request-\>setLocale\(\) is missing in documentation [\#4091](https://github.com/codeigniter4/CodeIgniter4/issues/4091)
- vars in .env sometimes returns null [\#3992](https://github.com/codeigniter4/CodeIgniter4/issues/3992)
- parseRequestURI dose not override globals\['server'\] ? [\#3976](https://github.com/codeigniter4/CodeIgniter4/issues/3976)
- Feature: Spark header Suppression [\#3918](https://github.com/codeigniter4/CodeIgniter4/issues/3918)
- Feature: AJAX filters don't work [\#2314](https://github.com/codeigniter4/CodeIgniter4/issues/2314)
- Request: Bulk route filters with parameters [\#2078](https://github.com/codeigniter4/CodeIgniter4/issues/2078)
- Need a global way to set config values dynamically [\#1661](https://github.com/codeigniter4/CodeIgniter4/issues/1661)
- Feature Request : support the db config instead of .env while the core is initialzed [\#1618](https://github.com/codeigniter4/CodeIgniter4/issues/1618)
- TODO Database BaseConnection needs better connections [\#1253](https://github.com/codeigniter4/CodeIgniter4/issues/1253)

**Merged pull requests:**

- Fix nullable type not showing in SQL string [\#4696](https://github.com/codeigniter4/CodeIgniter4/pull/4696) ([paulbalandan](https://github.com/paulbalandan))
- Add reference to cache repo [\#4694](https://github.com/codeigniter4/CodeIgniter4/pull/4694) ([MGatner](https://github.com/MGatner))
- Allow CI Environments [\#4692](https://github.com/codeigniter4/CodeIgniter4/pull/4692) ([MGatner](https://github.com/MGatner))
- Add URI cast [\#4691](https://github.com/codeigniter4/CodeIgniter4/pull/4691) ([MGatner](https://github.com/MGatner))
- MockCache::getCacheInfo\(\) [\#4689](https://github.com/codeigniter4/CodeIgniter4/pull/4689) ([MGatner](https://github.com/MGatner))
- Remove Psr\Cache [\#4688](https://github.com/codeigniter4/CodeIgniter4/pull/4688) ([MGatner](https://github.com/MGatner))
- Spacing issues [\#4686](https://github.com/codeigniter4/CodeIgniter4/pull/4686) ([MGatner](https://github.com/MGatner))
- \[Rector\] Update rector 0.11.2 and phpstan 0.12.86 [\#4685](https://github.com/codeigniter4/CodeIgniter4/pull/4685) ([samsonasik](https://github.com/samsonasik))
- Optimize CommandRunner and Commands [\#4683](https://github.com/codeigniter4/CodeIgniter4/pull/4683) ([paulbalandan](https://github.com/paulbalandan))
- Revert Actions minor version [\#4682](https://github.com/codeigniter4/CodeIgniter4/pull/4682) ([MGatner](https://github.com/MGatner))
- Revert Actions minor versioning [\#4681](https://github.com/codeigniter4/CodeIgniter4/pull/4681) ([MGatner](https://github.com/MGatner))
- Bump shivammathur/setup-php from 2 to 2.11.0 [\#4679](https://github.com/codeigniter4/CodeIgniter4/pull/4679) ([dependabot[bot]](https://github.com/apps/dependabot))
- Bump actions/checkout from 2 to 2.3.4 [\#4678](https://github.com/codeigniter4/CodeIgniter4/pull/4678) ([dependabot[bot]](https://github.com/apps/dependabot))
- \[Rector\] Update rector to 0.10.22, remove symplify/composer-json-manipulator [\#4677](https://github.com/codeigniter4/CodeIgniter4/pull/4677) ([samsonasik](https://github.com/samsonasik))
- URL Functions [\#4675](https://github.com/codeigniter4/CodeIgniter4/pull/4675) ([MGatner](https://github.com/MGatner))
- Remove unused imports [\#4674](https://github.com/codeigniter4/CodeIgniter4/pull/4674) ([paulbalandan](https://github.com/paulbalandan))
- Split URL Helper tests [\#4672](https://github.com/codeigniter4/CodeIgniter4/pull/4672) ([MGatner](https://github.com/MGatner))
- \[Rector\] Apply Rector: RemoveUnusedPrivatePropertyRector [\#4671](https://github.com/codeigniter4/CodeIgniter4/pull/4671) ([samsonasik](https://github.com/samsonasik))
- \[UG\] update line number for "managing apps" [\#4670](https://github.com/codeigniter4/CodeIgniter4/pull/4670) ([totoprayogo1916](https://github.com/totoprayogo1916))
- Add setLocale to UG [\#4669](https://github.com/codeigniter4/CodeIgniter4/pull/4669) ([MGatner](https://github.com/MGatner))
- UTC Time from timestamp [\#4668](https://github.com/codeigniter4/CodeIgniter4/pull/4668) ([MGatner](https://github.com/MGatner))
- PSR: Cache [\#4667](https://github.com/codeigniter4/CodeIgniter4/pull/4667) ([MGatner](https://github.com/MGatner))
- Limit cache filenames [\#4666](https://github.com/codeigniter4/CodeIgniter4/pull/4666) ([MGatner](https://github.com/MGatner))
- Use descriptive failure message for `assertLogged` [\#4665](https://github.com/codeigniter4/CodeIgniter4/pull/4665) ([paulbalandan](https://github.com/paulbalandan))
- \[Rector\] Use $containerConfigurator-\>import\(\) instead of "sets" Option [\#4664](https://github.com/codeigniter4/CodeIgniter4/pull/4664) ([samsonasik](https://github.com/samsonasik))
- Update rector/rector requirement from 0.10.19 to 0.10.21 [\#4663](https://github.com/codeigniter4/CodeIgniter4/pull/4663) ([dependabot[bot]](https://github.com/apps/dependabot))
- Spark header suppression [\#4661](https://github.com/codeigniter4/CodeIgniter4/pull/4661) ([MGatner](https://github.com/MGatner))
- Registrar and .env priority [\#4659](https://github.com/codeigniter4/CodeIgniter4/pull/4659) ([MGatner](https://github.com/MGatner))
- Reset Single Service [\#4657](https://github.com/codeigniter4/CodeIgniter4/pull/4657) ([MGatner](https://github.com/MGatner))
- Unify migration message format for `migrate` and `migrate:rollback` [\#4656](https://github.com/codeigniter4/CodeIgniter4/pull/4656) ([paulbalandan](https://github.com/paulbalandan))
- \[Scripts\] Make sure bash script still works in Windows [\#4655](https://github.com/codeigniter4/CodeIgniter4/pull/4655) ([paulbalandan](https://github.com/paulbalandan))
- change instance\(\) to  createFromInstance\(\) [\#4654](https://github.com/codeigniter4/CodeIgniter4/pull/4654) ([totoprayogo1916](https://github.com/totoprayogo1916))
- Add branch alias for develop branch [\#4652](https://github.com/codeigniter4/CodeIgniter4/pull/4652) ([paulbalandan](https://github.com/paulbalandan))
- Refactor URI detection [\#4651](https://github.com/codeigniter4/CodeIgniter4/pull/4651) ([MGatner](https://github.com/MGatner))
- \[Scipts\] Ensure admin/setup.sh exists before run bash admin/setup.sh [\#4650](https://github.com/codeigniter4/CodeIgniter4/pull/4650) ([samsonasik](https://github.com/samsonasik))
- Update rector/rector requirement from 0.10.17 to 0.10.19 [\#4649](https://github.com/codeigniter4/CodeIgniter4/pull/4649) ([dependabot[bot]](https://github.com/apps/dependabot))
- \[ci skip\] Remove ajax filter reference from docs. Fixes \#2314 [\#4648](https://github.com/codeigniter4/CodeIgniter4/pull/4648) ([lonnieezell](https://github.com/lonnieezell))
- Internal URI handling [\#4646](https://github.com/codeigniter4/CodeIgniter4/pull/4646) ([MGatner](https://github.com/MGatner))
- URI::removeDotSegments\(\) [\#4644](https://github.com/codeigniter4/CodeIgniter4/pull/4644) ([MGatner](https://github.com/MGatner))
- \[Rector\] Pin "nikic/php-parser": "4.10.4" [\#4642](https://github.com/codeigniter4/CodeIgniter4/pull/4642) ([samsonasik](https://github.com/samsonasik))
- Update to psr/log v1.1.4 [\#4641](https://github.com/codeigniter4/CodeIgniter4/pull/4641) ([paulbalandan](https://github.com/paulbalandan))
- Update rector/rector requirement from 0.10.15 to 0.10.17 [\#4639](https://github.com/codeigniter4/CodeIgniter4/pull/4639) ([dependabot[bot]](https://github.com/apps/dependabot))
- Update MockCache [\#4638](https://github.com/codeigniter4/CodeIgniter4/pull/4638) ([MGatner](https://github.com/MGatner))
- Cache Key Validation [\#4637](https://github.com/codeigniter4/CodeIgniter4/pull/4637) ([MGatner](https://github.com/MGatner))
- \[Cache\] Allow covariant returns and optimize code [\#4635](https://github.com/codeigniter4/CodeIgniter4/pull/4635) ([paulbalandan](https://github.com/paulbalandan))
- Refactor ComposerScripts [\#4634](https://github.com/codeigniter4/CodeIgniter4/pull/4634) ([paulbalandan](https://github.com/paulbalandan))
- Expand Time for interface [\#4633](https://github.com/codeigniter4/CodeIgniter4/pull/4633) ([MGatner](https://github.com/MGatner))
- Patch Log code [\#4631](https://github.com/codeigniter4/CodeIgniter4/pull/4631) ([MGatner](https://github.com/MGatner))
- Cache Returns Types [\#4630](https://github.com/codeigniter4/CodeIgniter4/pull/4630) ([MGatner](https://github.com/MGatner))
- Cache getMetadata\(\) Format [\#4629](https://github.com/codeigniter4/CodeIgniter4/pull/4629) ([MGatner](https://github.com/MGatner))
- fix\(cache\): add check for redis empty results in deleteMatching [\#4628](https://github.com/codeigniter4/CodeIgniter4/pull/4628) ([yassinedoghri](https://github.com/yassinedoghri))
- Update rector/rector requirement from 0.10.12 to 0.10.15 [\#4627](https://github.com/codeigniter4/CodeIgniter4/pull/4627) ([dependabot[bot]](https://github.com/apps/dependabot))
- Add fallback for Config\Cookie [\#4625](https://github.com/codeigniter4/CodeIgniter4/pull/4625) ([paulbalandan](https://github.com/paulbalandan))
- \[Test\] Use @codeCoverageIgnore for deprecated class/method [\#4623](https://github.com/codeigniter4/CodeIgniter4/pull/4623) ([samsonasik](https://github.com/samsonasik))
- Fix: Nested sections rendering [\#4622](https://github.com/codeigniter4/CodeIgniter4/pull/4622) ([iRedds](https://github.com/iRedds))
- Update rector/rector requirement from 0.10.11 to 0.10.12 [\#4621](https://github.com/codeigniter4/CodeIgniter4/pull/4621) ([dependabot[bot]](https://github.com/apps/dependabot))
- Update phpstan/phpstan requirement from 0.12.84 to 0.12.85 [\#4620](https://github.com/codeigniter4/CodeIgniter4/pull/4620) ([dependabot[bot]](https://github.com/apps/dependabot))
- Bump actions/github-script from v4.0.1 to v4.0.2 [\#4614](https://github.com/codeigniter4/CodeIgniter4/pull/4614) ([dependabot[bot]](https://github.com/apps/dependabot))
- Update rector/rector requirement from 0.10.9 to 0.10.11 [\#4613](https://github.com/codeigniter4/CodeIgniter4/pull/4613) ([dependabot[bot]](https://github.com/apps/dependabot))
- \[Rector\] Refactor UnderscoreToCamelCaseVariableNameRector with latest Rector compatible code [\#4612](https://github.com/codeigniter4/CodeIgniter4/pull/4612) ([samsonasik](https://github.com/samsonasik))
- \[Feat\]\[Autoloader\] Allow autoloading non-class files [\#4611](https://github.com/codeigniter4/CodeIgniter4/pull/4611) ([paulbalandan](https://github.com/paulbalandan))
- failValidationError can take an array of errors [\#4609](https://github.com/codeigniter4/CodeIgniter4/pull/4609) ([caswell-wc](https://github.com/caswell-wc))
- assertJsonFragment fails gracefully with invalid json [\#4608](https://github.com/codeigniter4/CodeIgniter4/pull/4608) ([caswell-wc](https://github.com/caswell-wc))
- Non-persistent fake [\#4607](https://github.com/codeigniter4/CodeIgniter4/pull/4607) ([caswell-wc](https://github.com/caswell-wc))
- Fix validation of array data [\#4606](https://github.com/codeigniter4/CodeIgniter4/pull/4606) ([paulbalandan](https://github.com/paulbalandan))
- Use realpath\(\) to fix app prioritization of validation messages [\#4605](https://github.com/codeigniter4/CodeIgniter4/pull/4605) ([paulbalandan](https://github.com/paulbalandan))
- Optimizations for Autoloader [\#4604](https://github.com/codeigniter4/CodeIgniter4/pull/4604) ([paulbalandan](https://github.com/paulbalandan))
- format style [\#4603](https://github.com/codeigniter4/CodeIgniter4/pull/4603) ([totoprayogo1916](https://github.com/totoprayogo1916))
- whitespaces [\#4602](https://github.com/codeigniter4/CodeIgniter4/pull/4602) ([totoprayogo1916](https://github.com/totoprayogo1916))
- \[Rector\] Apply Full PHP 7.3 Rector Set List \(Skip JsonThrowOnErrorRector & StringifyStrNeedlesRector\) [\#4601](https://github.com/codeigniter4/CodeIgniter4/pull/4601) ([samsonasik](https://github.com/samsonasik))
- Bump actions/github-script from v3 to v4.0.1 [\#4599](https://github.com/codeigniter4/CodeIgniter4/pull/4599) ([dependabot[bot]](https://github.com/apps/dependabot))
- Simplify Cookie Class [\#4596](https://github.com/codeigniter4/CodeIgniter4/pull/4596) ([mostafakhudair](https://github.com/mostafakhudair))
- Fix service methods …$params type [\#4594](https://github.com/codeigniter4/CodeIgniter4/pull/4594) ([najdanovicivan](https://github.com/najdanovicivan))
- Fix new service replacement service provider precedence on core factory implementations [\#4593](https://github.com/codeigniter4/CodeIgniter4/pull/4593) ([element-code](https://github.com/element-code))
- Update rector/rector requirement from 0.10.6 to 0.10.9 [\#4592](https://github.com/codeigniter4/CodeIgniter4/pull/4592) ([dependabot[bot]](https://github.com/apps/dependabot))
- Debug/Toolbar - Memory issue fix [\#4590](https://github.com/codeigniter4/CodeIgniter4/pull/4590) ([najdanovicivan](https://github.com/najdanovicivan))
- BaseModel - Add public getIdValue\(\) method [\#4589](https://github.com/codeigniter4/CodeIgniter4/pull/4589) ([najdanovicivan](https://github.com/najdanovicivan))
- Feature: Escaping array dot notation [\#4588](https://github.com/codeigniter4/CodeIgniter4/pull/4588) ([iRedds](https://github.com/iRedds))
- Update phpstan/phpstan requirement from 0.12.83 to 0.12.84 [\#4587](https://github.com/codeigniter4/CodeIgniter4/pull/4587) ([dependabot[bot]](https://github.com/apps/dependabot))
- extra \</li\> in tabs area [\#4586](https://github.com/codeigniter4/CodeIgniter4/pull/4586) ([jbrahy](https://github.com/jbrahy))
- user\_guide update: remove whitespaces, convert tabs to spaces & adjust the over-extended lines [\#4585](https://github.com/codeigniter4/CodeIgniter4/pull/4585) ([totoprayogo1916](https://github.com/totoprayogo1916))
- Fix: BaseModel. Removed duplicate code. [\#4581](https://github.com/codeigniter4/CodeIgniter4/pull/4581) ([iRedds](https://github.com/iRedds))
- Change Entity Namespace [\#4577](https://github.com/codeigniter4/CodeIgniter4/pull/4577) ([mostafakhudair](https://github.com/mostafakhudair))
- \[Rector\] Remove RemoveDefaultArgumentValueRector [\#4576](https://github.com/codeigniter4/CodeIgniter4/pull/4576) ([samsonasik](https://github.com/samsonasik))
- \[Rector\] Add Comment for reason RemoveDefaultArgumentValueRector copied to utils/Rector [\#4575](https://github.com/codeigniter4/CodeIgniter4/pull/4575) ([samsonasik](https://github.com/samsonasik))
- \[Rector\] Copy RemoveDefaultArgumentValueRector to utils/Rector [\#4574](https://github.com/codeigniter4/CodeIgniter4/pull/4574) ([samsonasik](https://github.com/samsonasik))
- Fix: Validation::withRequest\(\) with Content-Type: multipart/form-data [\#4571](https://github.com/codeigniter4/CodeIgniter4/pull/4571) ([iRedds](https://github.com/iRedds))
- \[Rector\] Update Rector 0.10.6, re-enable auto imports [\#4569](https://github.com/codeigniter4/CodeIgniter4/pull/4569) ([samsonasik](https://github.com/samsonasik))
- feat\(cache\): add deleteMatching method to remove multiple cache items [\#4567](https://github.com/codeigniter4/CodeIgniter4/pull/4567) ([yassinedoghri](https://github.com/yassinedoghri))
- Mysql connection issue with MYSQLI\_CLIENT\_SSL\_DONT\_VERIFY\_SERVER\_CERT [\#4566](https://github.com/codeigniter4/CodeIgniter4/pull/4566) ([fedeburo](https://github.com/fedeburo))
- Bump actions/cache from v2.1.4 to v2.1.5 [\#4564](https://github.com/codeigniter4/CodeIgniter4/pull/4564) ([dependabot[bot]](https://github.com/apps/dependabot))
- \[Rector\] Apply Rector: UnnecessaryTernaryExpressionRector [\#4563](https://github.com/codeigniter4/CodeIgniter4/pull/4563) ([samsonasik](https://github.com/samsonasik))
- Check intl extension loaded on check min PHP version [\#4562](https://github.com/codeigniter4/CodeIgniter4/pull/4562) ([samsonasik](https://github.com/samsonasik))
- Prefix calls to getenv\(\) during config resolution [\#4561](https://github.com/codeigniter4/CodeIgniter4/pull/4561) ([paulbalandan](https://github.com/paulbalandan))
- \[Rector\] Apply Rector: ChangeArrayPushToArrayAssignRector [\#4560](https://github.com/codeigniter4/CodeIgniter4/pull/4560) ([samsonasik](https://github.com/samsonasik))
- \[Rector\] Enable check tests/system/Models [\#4557](https://github.com/codeigniter4/CodeIgniter4/pull/4557) ([samsonasik](https://github.com/samsonasik))
- Debugging SQL Server in Actions [\#4554](https://github.com/codeigniter4/CodeIgniter4/pull/4554) ([paulbalandan](https://github.com/paulbalandan))
- Support for masking sensitive debug data [\#4550](https://github.com/codeigniter4/CodeIgniter4/pull/4550) ([pixobit](https://github.com/pixobit))
- Use message directly if intl is not available [\#4549](https://github.com/codeigniter4/CodeIgniter4/pull/4549) ([paulbalandan](https://github.com/paulbalandan))
- Add compatibility for strict types [\#4548](https://github.com/codeigniter4/CodeIgniter4/pull/4548) ([paulbalandan](https://github.com/paulbalandan))
- Removes deprecated settings in env file [\#4547](https://github.com/codeigniter4/CodeIgniter4/pull/4547) ([paulbalandan](https://github.com/paulbalandan))
- Fix wrong argument passed in doc [\#4546](https://github.com/codeigniter4/CodeIgniter4/pull/4546) ([paulbalandan](https://github.com/paulbalandan))
- Fix dot notation for if\_exist [\#4545](https://github.com/codeigniter4/CodeIgniter4/pull/4545) ([paulbalandan](https://github.com/paulbalandan))
- Relocate cookie exception [\#4544](https://github.com/codeigniter4/CodeIgniter4/pull/4544) ([mostafakhudair](https://github.com/mostafakhudair))
- \[Rector\] Apply RemoveDefaultArgumentValueRector [\#4543](https://github.com/codeigniter4/CodeIgniter4/pull/4543) ([samsonasik](https://github.com/samsonasik))
- Fix html formatting for exceptions and errors [\#4542](https://github.com/codeigniter4/CodeIgniter4/pull/4542) ([musmanikram](https://github.com/musmanikram))
- Create Config::Cookie Class [\#4508](https://github.com/codeigniter4/CodeIgniter4/pull/4508) ([mostafakhudair](https://github.com/mostafakhudair))

## [v4.1.1](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.0) (2021-02-01)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.1.0...v4.1.1)

**Fixed bugs:**

- Fixed an issue where **.gitattributes** was preventing framework downloads

## [v4.1.0](https://github.com/codeigniter4/CodeIgniter4/tree/v4.1.0) (2021-01-31)

[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.0.5...v4.1.0)

**Implemented enhancements:**

- Rector 0.9 fixes [\#4196](https://github.com/codeigniter4/CodeIgniter4/issues/#4196)
- Cannot declare class Config\App error on running PHPUnit [\#4114](https://github.com/codeigniter4/CodeIgniter4/issues/4114)
- Backfill non-optional parameters (https://github.com/codeigniter4/CodeIgniter4/pull/3938)
- Change deprecated assertFileNotExists (https://github.com/codeigniter4/CodeIgniter4/pull/3862)

See [CHANGELOG_4.0.md](./CHANGELOG_4.0.md)
