# Changelog

## [v4.7.0](https://github.com/codeigniter4/CodeIgniter4/tree/v4.7.0) (2026-02-01)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.6.5...v4.7.0)

### Breaking Changes

* feat: require double curly braces for placeholders in `regex_match` rule by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9597
* feat(cache): add `deleteMatching` method definition in CacheInterface by @yassinedoghri in https://github.com/codeigniter4/CodeIgniter4/pull/9809
* feat(cache): add native types to all CacheInterface methods by @yassinedoghri in https://github.com/codeigniter4/CodeIgniter4/pull/9811
* feat(entity): deep change tracking for objects and arrays by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9779
* feat(model): primary key validation by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9840
* feat(entity): properly convert arrays of entities in `toRawArray()` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9841
* feat: add configurable status code filtering for `PageCache` filter by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9856
* fix: inconsistent `key` handling in encryption by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9868
* refactor: complete `QueryInterface` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9892
* feat: add `remember()` to `CacheInterface` by @datamweb in https://github.com/codeigniter4/CodeIgniter4/pull/9875
* refactor: Use native return types instead of using `#[ReturnTypeWillChange]` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9900

### Fixed Bugs

* fix: ucfirst all cookie samesite values by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9564
* fix: controller attribute filters with parameters by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9769
* fix: Fixed test Transformers by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9778
* fix: signal trait by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9846

### New Features

* feat: signals by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9690
* feat(app): Added controller attributes by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/9745
* feat: API transformers by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/9763
* feat: FrankenPHP Worker Mode by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9889

### Enhancements

* feat: add email/smtp plain auth method by @ip-qi in https://github.com/codeigniter4/CodeIgniter4/pull/9462
* feat: rewrite `ImageMagickHandler` to rely solely on the PHP `imagick` extension by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9526
* feat: add `Time::addCalendarMonths()` and `Time::subCalendarMonths()` methods by @christianberkman in https://github.com/codeigniter4/CodeIgniter4/pull/9528
* feat: add `clearMetadata()` method to provide privacy options when using imagick handler by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9538
* feat: add `dns_cache_timeout` for option `CURLRequest` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9553
* feat: added `fresh_connect` options to `CURLRequest` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9559
* feat: update `CookieInterface::EXPIRES_FORMAT` to use date format per RFC 7231 by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9563
* feat: share connection & DNS Cache to `CURLRequest` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9557
* feat: add option to change default behaviour of `JSONFormatter` max depth by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9585
* feat: customizable `.env` directory path by @totoprayogo1916 in https://github.com/codeigniter4/CodeIgniter4/pull/9631
* feat: migrations lock by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9660
* feat: uniform rendering of stack trace from failed DB operations by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9677
* feat: make `insertBatch()` and `updateBatch()` respect model rules by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9708
* feat: add enum casting by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9752
* feat(app): Added pagination response to API ResponseTrait by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/9758
* feat: update robots definition for `UserAgent` class by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9782
* feat: added `async` & `persistent` options to Cache Redis by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9792
* feat: Add support for HTTP status in `ResponseCache` by @sk757a in https://github.com/codeigniter4/CodeIgniter4/pull/9855
* feat: prevent `Maximum call stack size exceeded` on client-managed requests by @datamweb in https://github.com/codeigniter4/CodeIgniter4/pull/9852
* feat: add `isPast()` and `isFuture()` time convenience methods by @datamweb in https://github.com/codeigniter4/CodeIgniter4/pull/9861
* feat: allow overriding namespaced views via `app/Views` directory by @datamweb in https://github.com/codeigniter4/CodeIgniter4/pull/9860
* feat: make DebugToolbar smarter about detecting binary/streamed responses by @datamweb in https://github.com/codeigniter4/CodeIgniter4/pull/9862
* feat: complete `Superglobals` implementation by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9858
* feat: encryption key rotation by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9870
* feat: APCu caching driver by @sk757a in https://github.com/codeigniter4/CodeIgniter4/pull/9874
* feat: added ``persistent`` config item to redis handler `Session` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9793
* feat: Add CSP3 `script-src-elem` directive by @mark-unwin in https://github.com/codeigniter4/CodeIgniter4/pull/9722
* feat: Add support for CSP3 keyword-sources by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9906
* feat: enclose hash-based CSP directive values in single quotes by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9908
* feat: add support for more CSP3 directives by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9909
* feat: add support for CSP3 `report-to` directive by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9910

### Refactoring

* refactor: cleanup code in `Email` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9570
* refactor: remove deprecated types in random_string() helper by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9592
* refactor: do not use future-deprecated `DATE_RFC7231` constant by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9657
* refactor: remove `curl_close` has no effect since PHP 8.0 by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9683
* refactor: remove `finfo_close` has no effect since PHP 8.1 by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9684
* refactor: remove `imagedestroy` has no effect since PHP 8.0 by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9688
* refactor: deprecated PHP 8.5 constant `FILTER_DEFAULT` for `filter_*()` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9699
* chore: bump minimum required `PHP 8.2` by @ddevsr in https://github.com/codeigniter4/CodeIgniter4/pull/9701
* refactor: add the `SensitiveParameter` attribute to methods dealing with sensitive info by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9710
* fix: Remove check ext-json by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9713
* refactor(app): Standardize subdomain detection logic by @lonnieezell in https://github.com/codeigniter4/CodeIgniter4/pull/9751
* refactor: Types for `BaseModel`, `Model` and dependencies by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9830
* chore: remove IncomingRequest deprecations by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9851
* refactor: Session library by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9831
* refactor: Superglobals - remove property promotion and fix PHPDocs by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9871
* refactor: Rework `Entity` class by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9878
* refactor: compare `$db->connID` to `false` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9891
* refactor: cleanup `ContentSecurityPolicy` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9904
* refactor: deprecate `CodeIgniter\HTTP\ContentSecurityPolicy::$nonces` since never used by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9905

For the changelog of v4.6, see [CHANGELOG_4.6.md](./changelogs/CHANGELOG_4.6.md).<br/>
For the changelog of v4.5, see [CHANGELOG_4.5.md](./changelogs/CHANGELOG_4.5.md).<br/>
For the changelog of v4.4, see [CHANGELOG_4.4.md](./changelogs/CHANGELOG_4.4.md).<br/>
For the changelog of v4.3, see [CHANGELOG_4.3.md](./changelogs/CHANGELOG_4.3.md).<br/>
For the changelog of v4.2, see [CHANGELOG_4.2.md](./changelogs/CHANGELOG_4.2.md).<br/>
For the changelog of v4.1, see [CHANGELOG_4.1.md](./changelogs/CHANGELOG_4.1.md).<br/>
For the changelog of v4.0, see [CHANGELOG_4.0.md](./changelogs/CHANGELOG_4.0.md).
