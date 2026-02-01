# Changelog 4.6

## [v4.6.5](https://github.com/codeigniter4/CodeIgniter4/tree/v4.6.5) (2026-02-01)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.6.4...v4.6.5)

### Fixed Bugs

* fix: make seeder to respect database group by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9886
* fix: ensure CSP nonces are Base64 encoded by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9907

### Refactoring

* refactor: debugbar time header not dependent on locale by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9880
* refactor: Remove dead code from MySQLi Connection related to PHP 5 by @kamil-tekiela in https://github.com/codeigniter4/CodeIgniter4/pull/9887
* refactor: Clean up mysqli transactions by @kamil-tekiela in https://github.com/codeigniter4/CodeIgniter4/pull/9888

## [v4.6.4](https://github.com/codeigniter4/CodeIgniter4/tree/v4.6.4) (2025-12-12)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.6.3...v4.6.4)

### Fixed Bugs

* fix: prevent non-shared DB instances from polluting shared cache by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9679
* fix: `Connection::getFieldData()` default value convention for `SQLSRV` and `OCI8` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9680
* fix: `Forge::modifyColumn()` for Postgre handler by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9676
* fix: setting `created_at` field in `Model::replace()` method by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9693
* fix: Casting in insertBatch and updateBatch methods. by @patel-vansh in https://github.com/codeigniter4/CodeIgniter4/pull/9698
* fix: `compileOrderBy()` method by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9697
* fix: SQLite3 password handling for empty string by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9729
* fix: TypeError in `valid_base64` rule when checking invalid base64 strings by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9733
* fix: debug toolbar logs collector behavior on `isEmpty()` by @mjomble in https://github.com/codeigniter4/CodeIgniter4/pull/9724
* fix: crash in `toggleViewsHints` - `debugDiv.appendChild` (`toolbar.js`) by @mjomble in https://github.com/codeigniter4/CodeIgniter4/pull/9735
* fix: cannot read properties of null in `toggleViewsHints` (`toolbar.js`) by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9736
* fix: type error in controlled cell by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9784
* fix: handle resources and closures in JSON exception responses by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9788
* fix: quote reserved keyword `timestamp` used as a field name for session table by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9805
* fix: Add an IDs for toolbar form fields by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9823
* fix: disable echo in the preload file by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9825
* fix(cache): prevent Redis error when `deleteMatching()` finds no keys by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9829

### Refactoring

* refactor: change `$request` to `CLIRequest|IncomingRequest` in `ResponseTrait` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9658
* refactor: fix phpdoc and improve code in `Language` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9656
* refactor: remove redundant property declarations in `BaseController` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9659
* refactor: update `CheckPhpIni` code by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9672
* refactor: Improve types for phpstan by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9685
* refactor: fix phpstan issues on magic properties by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9728
* refactor: use `superglobals` service in the `UserAgent` class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9783

## [v4.6.3](https://github.com/codeigniter4/CodeIgniter4/tree/v4.6.3) (2025-08-02)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.6.2...v4.6.3)

### Fixed Bugs

* fix: CID check in Email class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9645
* fix: SMTP connection resource validation in `Email` class destructor by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9648

### Refactoring

* refactor: update preload script to exclude `util_bootstrap` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9649
* refactor: phpdoc for `Config\Filters::$globals` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9652

## [v4.6.2](https://github.com/codeigniter4/CodeIgniter4/tree/v4.6.2) (2025-07-26)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.6.1...v4.6.2)

### Security

* **ImageMagickHandler**: *Command Injection Vulnerability in ImageMagick Handler*
    Fixes a vulnerability relating to uses of `ImageMagickHandler`'s `resize()` or `text()` methods
    where an attacker can upload malicious filenames containing shell metacharacters that get executed when
    the image is processed or when text is added to the image.

    See the [security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-9952-gv64-x94c)
    for details. Credits to @vicevirus for reporting the issue.

### Fixed Bugs

* chore: add missing EscaperInterface to the AutoloadConfig by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9561
* fix: remove service dependency from sanitize_filename() helper function by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9560
* fix: use native PHP truthiness for condition evaluation in when()/whenNot() by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9576
* fix: add error handling for corrupted cache files in `FileHandler` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9586
* fix: correct `getHostname()` fallback logic in `Email` class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9587
* fix: encapsulation violation in `BasePreparedQuery` class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9603
* fix: URI authority generation for schemes without default ports by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9605
* fix: correct path parsing in `SiteURIFactory::parseRequestURI()` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9613
* fix: support for multibyte folder names when the app is served from a subfolder by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9615
* fix: use correct 24-hour time format in development error page. by @ping-yee in https://github.com/codeigniter4/CodeIgniter4/pull/9628
* fix: improve CURLRequest intermediate HTTP response handling by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9627
* fix: ensure `make:test` works on Windows by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9635
* fix: ensure `make:test` generates test files ending in `Test` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9636
* fix: `make:test` requires 3 inputs after entering an empty class name by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9637
* fix: add filename parameters to inline Content-Disposition headers by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9638

### Refactoring

* refactor: add `system/util_bootstrap.php` to curb overreliance to `system/Test/bootstrap.php` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9562
* refactor: update places to use `system/util_bootstrap.php` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9568
* refactor: more accurate array PHPDocs of Cookie by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9569
* refactor: use native phpdocs wherever possible by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9571
* refactor: fix `notIdentical.alwaysTrue` error by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9579
* refactor: fix phpstan errors in `Events` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9580
* refactor: fix non-booleans in if conditions by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9578
* refactor: fix and micro-optimize code in `Format` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9583
* refactor: fix various phpstan errors in Log component by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9581
* refactor: partial fix errors on Email by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9582
* refactor: fix phpstan errors in `ResponseTrait` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9591
* refactor: precise PHPDocs for Autoloader by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9593
* refactor: fix phpstan errors in mock classes by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9594
* refactor: fix various phpstan errors in Cache by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9610
* fix: apply rector rule TernaryImplodeToImplodeRector by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9614
* refactor: `Console::showHeader()` call `date()` only once by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9616

## [v4.6.1](https://github.com/codeigniter4/CodeIgniter4/tree/v4.6.1) (2025-05-02)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.6.0...v4.6.1)

### Fixed Bugs

* fix(CURLRequest): multiple header sections after redirects by @ducng99 in https://github.com/codeigniter4/CodeIgniter4/pull/9426
* fix: set headers for CORS by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9437
* fix: upsert with composite unique index by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9454
* fix: `getVersion()` for OCI8 and SQLSRV drivers by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9471
* fix: Toolbar when `maxHistory` is set to `0` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9506
* fix: `Session::markAsTempdata()` adding wrong TTL by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9536
* fix: added "application/octet-stream" to the "stl" mime type in the M… by @Franky5831 in https://github.com/codeigniter4/CodeIgniter4/pull/9543

### Refactoring

* refactor: get upper first protocol only one call in Email by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9449
* refactor: PHPDocs in `env()` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9468
* refactor: remove lowercase event name for logging by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9483
* refactor: OCI8 `limit()` method by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9472
* refactor: deprecate redundant `FileHandler` cache methods by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9511
* refactor: fix `variable.undefined` (and other) errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9513
* refactor: fix `return.unusedType` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9514
* refactor: add `CITestStreamFilter` to phpstan-analysed list and fix errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9515
* refactor: fix `property.protected` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9517
* refactor: fix `function.alreadyNarrowedType` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9518
* refactor: fix `empty.property` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9519
* refactor: import FQCNs by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9520
* refactor: fix `isset.property` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9522
* refactor: fix `missingType.return` errors by @warcooft in https://github.com/codeigniter4/CodeIgniter4/pull/9523
* refactor: fix `nullCoalesce.variable` errors by @warcooft in https://github.com/codeigniter4/CodeIgniter4/pull/9524
* refactor: fix phpstan errors in `URI` and `SiteURI` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9525
* refactor: fix `@readonly` property errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9529
* refactor: fix `missingType.return` errors in system files by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9530
* refactor: fix `codeigniter.modelArgumentType` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9533
* refactor: fix `Session` and `SessionInterface` code by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9535

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

For the changelog of v4.5, see [CHANGELOG_4.5.md](./changelogs/CHANGELOG_4.5.md).
