# Changelog

## [v4.7.5](https://github.com/codeigniter4/CodeIgniter4/tree/v4.7.5) (2026-10-09)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.7.4...v4.7.5)

### Security

* **CURLRequest:** Request-specific headers, body, and options
  are now cleared when a request throws an exception and ``shareOptions`` is ``false``.
  Previously, reusing the client after a failed request could send credentials
  or private data to a different destination. Options passed to the constructor
  remain available for subsequent requests.
  Per-request ``baseURI`` and ``delay`` values are also reset after successful
  and failed requests, preventing URI components from leaking into later requests.
  When retrying a failed request, pass its options again, or reapply settings
  made with ``setAuth()``, ``setBody()``, ``setForm()``, or ``setJSON()``.
  See the `Security advisory GHSA-9g9v-xgwj-697h <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-9g9v-xgwj-697h>`_
  for more information.
* **Validation:** The ``is_image`` and ``mime_in`` file upload rules now reject client filenames ending in dots while
  continuing to accept genuinely extensionless uploads. These rules and ``ext_in`` also reject filenames with a PHP
  handler extension before the final extension, such as ``shell.php.gif``, including extensions revealed by filename
  sanitization. These checks also reject innocent filenames such as ``logo.php.gif``. See
  :doc:`Upgrading from 4.7.4 to 4.7.5 </installation/upgrade_475>` for migration instructions.
  Previously, a GIF/PHP polyglot with one of these names could pass validation. Applications that save uploads with 
  client-provided names in executable public directories may be affected. Use a generated filename or disable script 
  execution in the upload directory. See the `Security advisory GHSA-4hwf-v4mp-hf6c <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-4hwf-v4mp-hf6c>`_
  for more information.
* **Validation:** File validation rules now check every non-empty file in an
  optional multiple upload. Previously, an entry with ``UPLOAD_ERR_NO_FILE``
  could cause ``max_size``, ``is_image``, ``mime_in``, ``ext_in``, ``max_dims``,
  and ``min_dims`` to accept the upload without checking later files.
  ``max_dims`` and ``min_dims`` also return ``false`` for upload errors other
  than ``UPLOAD_ERR_NO_FILE`` before reading image dimensions.
  See the `Security advisory GHSA-cwxp-v62r-xwx2 <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-cwxp-v62r-xwx2>`_
  for more information.
* **View Parser:** Fixed a stored XSS vulnerability where a substituted value containing
  Parser syntax (e.g., ``{text|raw}`` or ``{!text!}``) was interpreted by a later
  substitution pass, allowing user-supplied data to bypass auto-escaping of other
  pseudo-variables. Substituted values are now treated strictly as data and are never
  parsed again. The same protection applies to output from built-in Parser plugins,
  including URLs and validation messages. See :ref:`upgrade-475-view-parser-values` for more information.
  See the `Security advisory GHSA-vgrf-mv2j-gp8w <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-vgrf-mv2j-gp8w>`_
  for more information.
* **View Parser:** Added restricted conditionals as an opt-in mitigation for
  *Code Injection through Parser Template Source*. Conditions in ``{if ...}`` and
  ``{elseif ...}`` tags are evaluated as PHP, so anyone who can edit Parser template
  source can execute PHP code. This remains the default behavior. Applications that
  let less-trusted users edit Parser templates must enable the new
  ``Config\View::$restrictParserConditionals`` setting or the ``restrictConditionals``
  render option. Restricted conditionals only limit this execution path; they do not
  make every template feature safe for less-trusted authors.
  The restriction also applies to nested renders on the same Parser instance.
  See :ref:`parser-restricting-conditionals` and the
  `Security advisory GHSA-4q58-jw8x-8cm7 <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-4q58-jw8x-8cm7>`_
  for more information.
* **Views:** Fixed a code execution vulnerability caused by view data keys
  overriding renderer-local variables. Data named ``template`` in ``Parser`` or
  ``view`` in ``View::renderString()`` could replace the source evaluated as PHP.
  Data named ``foundView`` in view cells could replace the selected file path
  and cause unintended local file inclusion. These issues require applications
  to pass less-trusted data under the affected keys.
  See the `Security advisory GHSA-c29x-ffjj-8r7x <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-c29x-ffjj-8r7x>`_
  for more information.

### Fixed Bugs

* fix: safely interpolate logger context values by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10384
* fix: resolve global state pollution causing random-order test failures in HTTP suite by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10372
* fix: omit empty CSP headers by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10412
* fix: remove destructive setServerArray([]) in FiltersTest::setUp to prevent random-order test failures by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10425
* fix: resolve TypeError in CLIRequest::parseCommand when argv is missing by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10415
* fix: resolve race conditions in Redis TTL and prevent cache test state leakage by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10374
* fix: handle null results in get_dir_file_info() by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10407
* fix: skip unreadable `.env` in `Boot::loadDotEnv` for separate-process tests by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10417
* fix: return 403 for Honeypot bot detection by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10490
* fix: pass prompt text to readline in `CLI::prompt()` so backspace does not erase it by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10507
* fix(Cookie): validate raw cookie values by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10516
* fix(CodeIgniter): prevent gatherOutput from being called twice when controller returns Response by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10369
* fix: Memcached decrement() gives the wrong sign on a missing key by @mdalikadar in https://github.com/codeigniter4/CodeIgniter4/pull/10512
* fix: correct file permissions and chmod target in File and UploadedFile move() by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10519
* fix: strip the table prefix correctly when rebuilding a SQLite3 table by @karlgray in https://github.com/codeigniter4/CodeIgniter4/pull/10509
* fix: write the prompt to STDOUT before readline() on Windows by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10534
* fix(Cookie): validate cookie path and domain attributes by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10527
* fix(I18n): compute correct date in Time::today(), yesterday(), and tomorrow() across timezones by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10532
* fix: return an empty string when readline() reaches end-of-file by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10542
* fix: reject unsupported database drivers in `make:migration --session` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10546
* fix(Test): reset `is_windows()` mock state in `CIUnitTestCase::tearDown()` by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10554
* fix(Cache): set secure 0755 permissions mode when creating directory in `FileVarExportHandler` by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10555
* fix: stop `FileLocatorCached` from restoring a deleted cache on shutdown by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10559
* fix: preserve sub-second precision in `BaseModel` datetime timestamps by @wakqasahmed in https://github.com/codeigniter4/CodeIgniter4/pull/10561
* fix(Debug): treat zero time as valid timer start by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10569
* fix: reject non-digit characters in `valid_cc_number()` by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10580
* fix: throw Postgre query errors with warnings disabled by @wakqasahmed in https://github.com/codeigniter4/CodeIgniter4/pull/10573
* fix: prevent infinite loop in `word_wrap()` with a character limit below 2 by @mdalikadar in https://github.com/codeigniter4/CodeIgniter4/pull/10596
* fix: resolve schema-qualified table names in `getFieldData()` and `protectIdentifiers()` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10604
* fix: cast Postgre batch subquery values so `updateBatch()` and `deleteBatch()` accept mixed PHP types by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10601
* fix: cast OCI8 batch subquery values so `updateBatch()` and `deleteBatch()` accept mixed PHP types by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10602
* fix: avoid undefined `STDERR` and `STDIN` outside the CLI by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10605

### Refactoring

* refactor(HTTP): split setCURLOptions into category helper methods to reduce complexity by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10339
* refactor: use `array-key` as the benevolent union of `int|string` in array shapes by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10396
* refactor: Enable Rector Code Quality set by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/10429
* refactor: fix uses of `empty()` calls by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10439
* refactor: fix `method.alreadyNarrowedType` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10440
* refactor: cleanup the Images library by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10441
* refactor: add generics to `Entity` and `DataCaster` casts by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10442
* refactor: fix `ternary.shortNotAllowed` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10445
* refactor: fix `missingType.property` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10447
* refactor: fix `return.type` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10448
* refactor: add generics to `Forge`, `BaseBuilder` and `BasePreparedQuery` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10449
* refactor: fix `nullCoalesce.property` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10450
* refactor: add precise return type to `CodeIgniter::getPerformanceStats()` by @soccerlover29 in https://github.com/codeigniter4/CodeIgniter4/pull/10458
* refactor: fix `property.nonObject` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10451
* refactor: sidestep PhpStorm warning on `@method` docblock in `Model` by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10444
* refactor: fix phpstan errors in `IncomingRequestTest` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10452
* refactor: fix phpstan errors in `Helpers` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10459
* refactor: fix phpstan errors in `Router` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10460
* refactor: clear out phpstan errors in `Validation` source and tests by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10461
* refactor: clear out phpstan errors in `HTTP` source and tests by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10462
* refactor: fix phpstan errors in `BaseBuilder` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10466
* refactor: add precise parameter types to Router test fixtures by @soccerlover29 in https://github.com/codeigniter4/CodeIgniter4/pull/10464
* refactor: fix phpstan errors in `Forge` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10470
* refactor: fix phpstan errors in `Result` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10473
* refactor: fix phpstan errors in `Connection` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10475
* refactor: fix phpstan errors in `PreparedQuery` and `Utils` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10476
* refactor: fix phpstan errors in `Config`, `Database`, `Query`, `MigrationRunner`, and `SQLite3\Table` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10477
* refactor: fix phpstan errors in `Debug` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10478
* refactor: fix phpstan errors in `Test` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10479
* refactor: widen `Response` body and `Formatter` data typing to `mixed` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10480
* refactor: fix remaining errors in `Helpers`' tests by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10481
* refactor: fix phpstan errors in `Config` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10482
* refactor: fix phpstan errors in `Commands` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10483
* refactor: fix phpstan errors in `View` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10484
* refactor: fix phpstan errors in `Filters` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10485
* refactor: fix phpstan errors in `Encryption` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10486
* refactor: fix iterable types in `Email` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10487
* refactor: fix iterable types in `Router` tests by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10488
* refactor: fix remaining phpstan errors in `Database` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10492
* refactor: fix phpstan errors in `I18n` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10493
* refactor: fix phpstan errors in `DataConverter` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10494
* refactor: fix phpstan errors in `AutoReview` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10495
* refactor: fix missing parameter types by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10496
* refactor: fix remaining missing iterable types by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10497
* refactor: fix remaining not found methods by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10498
* refactor: fix the remaining fixable `assign.propertyType` and `phpdoc.propertyType` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10499
* refactor: fix remaining `argument.type` and `method.childParameterType` errors by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10502
* refactor: replace anonymous class with bound closure in `PropertiesTrait` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10504
* refactor: bound phpstan's analysed PHP versions by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10505
* refactor: Enable MustHaveReturnTypeFunctionRule StructArmed rule on system/Helpers by @samsonasik in https://github.com/codeigniter4/CodeIgniter4/pull/10521
* refactor: fix minor type inaccuracies found via PHPStan bleeding edge by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10522
* refactor: fix return type covariance of `FileCollection::getIterator()` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10558
* refactor: tighten docblock return types in Result, CLIRequest, url_helper and IncomingRequest by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10588
* refactor: pass the session TTL to `Redis::set()` as an options array by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10600

## [v4.7.4](https://github.com/codeigniter4/CodeIgniter4/tree/v4.7.4) (2026-07-07)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.7.3...v4.7.4)

### Security

* **IncomingRequest:** *HTTPS detection via client-supplied headers* was fixed.
  ``IncomingRequest::isSecure()`` now trusts the ``X-Forwarded-Proto`` and
  ``Front-End-Https`` headers only when the request comes from a trusted proxy
  configured in ``Config\App::$proxyIPs``.
  See the `Security advisory GHSA-7wmf-pw8j-mc78 <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-7wmf-pw8j-mc78>`_
  for more information.

* **Query Builder:** Fixed a SQL injection vulnerability in ``deleteBatch()``.
  When ``deleteBatch()`` was used together with ``where()`` conditions, the
  bound values from the WHERE clause were substituted into the generated SQL
  with their escape flag ignored, so they were never escaped or quoted. The
  WHERE binds are now escaped in the same way as a regular ``delete()``.

  See the `Security advisory GHSA-c9w5-rwh3-7pm9 <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-c9w5-rwh3-7pm9>`_
  for more information.

* **UploadedFile:** ``UploadedFile::move()`` now sanitizes the client-provided
  filename when called without a second argument. Previously, the unsanitized
  client filename was used as the default, allowing path traversal sequences
  (e.g. ``../../public/shell.php``) to write the uploaded file outside the
  intended directory. A name explicitly passed as the second argument is not
  sanitized and remains the caller's responsibility.
  See the `Security advisory GHSA-hhmc-q9hp-r662 <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-hhmc-q9hp-r662>`_
  for more information.

* **Validation:** The ``is_image`` and ``mime_in`` file upload validation rules
  now also verify non-empty client filename extensions. Previously, these rules
  classified an upload solely by its content-derived MIME type, so a file with a
  dangerous client extension (for example, a ``.php`` file prepended with image
  magic bytes) could pass validation while keeping its original extension on
  disk.
  See the `Security advisory GHSA-mmj4-63m4-r6h5 <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-mmj4-63m4-r6h5>`_
  for more information.

  The ``is_image`` rule now rejects uploads when a non-empty client filename
  extension is not an image extension. The ``mime_in`` rule now rejects uploads
  when a non-empty client filename extension does not match the detected file
  content, matching the same extension/content agreement used by ``ext_in``.
  Files uploaded without any extension (such as JavaScript ``Blob`` uploads)
  are still accepted, since the rules validate the file content.

### Fixed Bugs

* fix: prevent updateBatch with existing where conditions by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10236
* fix: detect Safari version from Version token by @memleakd in https://github.com/codeigniter4/CodeIgniter4/pull/10251
* fix: nested transformer request scope leakage by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10281
* fix(model): pass $recursive parameter to parent in objectToRawArray by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10258
* fix: preserve enclosing stream filter when using `MockInputOutput` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10307
* fix: skip already-translated keys in `spark lang:find` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10308
* fix(Filters): check both keys and values in `InvalidChars` arrays by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10303
* fix: use dynamic lockMaxRetries limit in RedisHandler by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10295
* fix: preserve sub-namespace when generating Entity from Model (i.e., `--return entity`) by @xgrind in https://github.com/codeigniter4/CodeIgniter4/pull/10232
* fix: `env()` TypeError for non-string `$_SERVER` values + `esc()` fixes by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10305
* fix: unify casing in BaseService::injectMock by @ThomasMeschke in https://github.com/codeigniter4/CodeIgniter4/pull/10316
* fix: normalize SodiumHandler params and padding failures by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10321
* fix(Validation): correct required_without logic and prevent array key warnings by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10328
* fix: handle null ini values in phpini check by @Will-thom in https://github.com/codeigniter4/CodeIgniter4/pull/10233
* fix: preserve zero values in XML export by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10367
* fix: support array indexes in `getPostGet()` and `getGetPost()` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10362

### Refactoring

* refactor: narrow `Image` original dimension types to `int` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10326
* refactor(HTTP): optimize file path parsing in `download()` method by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10330
* refactor(database): optimize groupGetType by caching it inside BaseBuilder loops by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10340
* refactor: bump to phpstan-codeigniter v2.1 by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10312
* refactor: replace type `mixed` with more specific types by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10345
* refactor: optimize `prepQuotedPrintable()` with hash lookup and int-length tracking by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10344
* refactor: add precise `array` phpdocs for `CLI` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10354
* refactor(database): optimize `_processForeignKeys()` string allocations and loop invariants by @gr8man in https://github.com/codeigniter4/CodeIgniter4/pull/10351

## [v4.7.3](https://github.com/codeigniter4/CodeIgniter4/tree/v4.7.3) (2026-05-22)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.7.2...v4.7.3)

### Security

* **Validation**: *Uploaded file extension validation bypass in `ext_in` rule*
    The ``ext_in`` file upload validation rule now validates the client filename extension and verifies that it
    matches the detected MIME type. Previously, ``ext_in`` only checked the MIME-derived guessed extension, so
    a file with a mismatched client extension could pass validation.

    See the [GHSA-2gr4-ppc7-7mhx security advisory](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-2gr4-ppc7-7mhx) for more information. Credits to @z3moo and @teebow1e for reporting the issue.

### Fixed Bugs

* fix: make Autoloader composer path injectable to fix parallel test race condition by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10082
* fix: store SPL closures in `register()` so `unregister()` can remove them by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10097
* fix: ensure output buffer is closed after use of `command()` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10099
* fix: preserve null values in Validation::getValidated() by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10101
* fix: refactor inconsistent behavior on `CLI::write()` and `CLI::error()` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10106
* fix: ensure calling `env` command with options only would not throw by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10114
* fix: suppress stty stderr leak in `CLI::generateDimensions()` when stdin is not a TTY by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10124
* fix: reset Kint CSP state in worker mode by @memleakd in https://github.com/codeigniter4/CodeIgniter4/pull/10139
* fix: make `Time::createFromTimestamp` locale-independent by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10151
* fix: SQLSRV driver's `decrement()` method by @patel-vansh in https://github.com/codeigniter4/CodeIgniter4/pull/10155
* fix: suppress tput stderr leak when TERM is not present by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10167
* fix: support third-party loggers in toolbar logs collector by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10173
* fix: PostgreSQL Builder's `increment()` and `decrement()` methods not working for numeric columns by @patel-vansh in https://github.com/codeigniter4/CodeIgniter4/pull/10172
* fix: preserve cached table list shape by @memleakd in https://github.com/codeigniter4/CodeIgniter4/pull/10179
* fix: harden regex matching on `key:generate` command by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10183
* fix: restore deep dot-notation traversal in `Language::getLine()` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10189
* fix: make frankenphp-worker.php template idempotent on watcher restart by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10191
* fix: `Entity::normalizeValue()` must handle `UnitEnum` before `toArray()` by @maniaba in https://github.com/codeigniter4/CodeIgniter4/pull/10137
* fix: recognize off zlib output compression value by @memleakd in https://github.com/codeigniter4/CodeIgniter4/pull/10193
* fix: escape `--host` option in `serve` command by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10203

### Refactoring

* refactor: add full testing for `logs:clear` command by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10090
* refactor: add full testing for `debugbar:clear` command by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10093
* refactor: pass `--do-not-cache-result` to prevent shared cache corruption by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10098
* refactor: add full testing for `cache:clear` command by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10094
* refactor: rename `-h` option of `routes` command as `--handler` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10113
* refactor: further rename `--handler` to `--sort-by-handler` for `routes` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10125
* refactor: UX: `ClearLogs::execute()` error message is misleading after interactive `'n'` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10126
* refactor: simplify `FileLocator::listFiles()` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10142
* refactor: reduce PHPStan child return type baseline by @memleakd in https://github.com/codeigniter4/CodeIgniter4/pull/10165
* refactor: remove PHPStan callable signature baseline by @memleakd in https://github.com/codeigniter4/CodeIgniter4/pull/10166

## [v4.7.2](https://github.com/codeigniter4/CodeIgniter4/tree/v4.7.2) (2026-03-24)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.7.1...v4.7.2)

### Fixed Bugs

* fix: preserve JSON body when CSRF token is sent in header by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10064

## [v4.7.1](https://github.com/codeigniter4/CodeIgniter4/tree/v4.7.1) (2026-03-22)
[Full Changelog](https://github.com/codeigniter4/CodeIgniter4/compare/v4.7.0...v4.7.1)

### Breaking Changes

* fix: SQLite3 config type handling for `.env` overrides by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10037

### Fixed Bugs

* fix: escape CSP nonce attributes in JSON responses by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9938
* fix: correct `savePath` check in `MemcachedHandler` constructor by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9941
* fix: preserve index field in `updateBatch()` when `updateOnlyChanged` is `true` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9944
* fix: Hardcoded CSP Nonce Tags in ResponseTrait by @patel-vansh in https://github.com/codeigniter4/CodeIgniter4/pull/9937
* fix: initialize standalone toolbar by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9950
* fix: add fallback for `appOverridesFolder` config in View by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9958
* fix: avoid double-prefixing in `BaseConnection::callFunction()` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9959
* fix: generate inputs for all route params in Debug Toolbar by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9964
* fix: preserve Postgre casts when converting named placeholders in prepared queries by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9960
* fix: prevent extra query and invalid size in `Model::chunk()` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9961
* fix: worker mode events cleanup by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9997
* fix: add nonce to script-src-elem and style-src-elem when configured by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9999
* fix: `FeatureTestTrait::withRoutes()` may throw all sorts of errors on invalid HTTP methods by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/10004
* fix: validation when key does not exists by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10006
* fix: handle HTTP/2 responses without a reason phrase in CURLRequest by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10050

### Refactoring

* chore: signature for the `$headers` param in `FeatureTestTrait::withHeaders()` by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9932
* refactor: implement development versions for `CodeIgniter::CI_VERSION` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9951
* feat: Add `builds next` option by @neznaika0 in https://github.com/codeigniter4/CodeIgniter4/pull/9946
* refactor: use `__unserialize` instead of `__wakeup` in `TimeTrait` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9957
* refactor: remove `Exceptions::isImplicitNullableDeprecationError` by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9965
* refactor: fix `Security` test fail by itself by @paulbalandan in https://github.com/codeigniter4/CodeIgniter4/pull/9969
* refactor: make random-order API tests deterministic by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9983
* refactor: make random-order CLI tests deterministic by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/9998
* refactor: fix phpstan no type specified ValidationModelTest by @adiprsa in https://github.com/codeigniter4/CodeIgniter4/pull/10008
* refactor: fix dependency on test execution order by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10014
* refactor: update tests with old entities definition by @michalsn in https://github.com/codeigniter4/CodeIgniter4/pull/10026

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
