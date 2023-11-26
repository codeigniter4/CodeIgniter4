# Changelog

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

**Full Changelog**: https://github.com/codeigniter4/CodeIgniter4/compare/v4.4.1...v4.4.2

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
