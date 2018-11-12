##########
Change Log
##########

Version |version|
====================================================

Release Date: Not Released

**Next alpha release of CodeIgniter4**

The list of changed files follows, with PR numbers shown.

- admin/
	- pre-commit #1388
	- setup.sh #1388

- application /
	- Config/Autoload #1396, #1416
	- Config/Mimes #1368
	- Filters/Honeypot #1376
	- Views/
		- errors/* #1415, #1413
		- form.php removed #1442

- public /
	- index.php #1388

- system /
	- Cache/Handlers/
		- MemcachedHandler #1383
	- CLI/
		- CLI #1432
	- Commands/
		- Database/
			- CreateMigration #1374, #1422, #1431
			- MigrateCurrent #1431
			- MigrateLatest #1431
			- MigrateRollback #1431
			- MigrateStatus #1431
			- MigrateVersion #1431
		- Sessions/CrateMigration #1357
	- Config/
		- AutoloadCOnfig #1416
		- Services #1180
	- Database/
		- BaseBuilder #1335
		- BaseConnection #1335, #1407
		- BaseResult #1426
		- Forge #1343
		- MigrationRunner #1371
		- MySQLi/Connection #1335
		- MySQLi/Forge #1343, #1344
		- Postgre/Connection #1335
		- SQLite3/Connection #1335
	- Debug
		- Toolbar #1370
	- Email/
		- Email #1389, #1413
	- Files/
		- File #1399
	- Helpers/
		- array_helper #1412
	- HTTP/
		- DownloadResponse #1375
		- Exceptions/DownloadException #1405
		- Files/UploadedFile #1335, #1399
		- RedirectResponse #1387
		- ResponseInterface #1384
		- UploadedFile #1368
		- URI #1213
	- Language/en/
		- Database #1335
		- Filters #1378
		- Migrations #1374
	- Pager/
		- Pager #1213
		- PagerRenderer #1213
	- Router/
		- RouteCollectionInterface #1406, #1410
	- Session/Handlers/
		- BaseHandler #1180
		- DatabaseHandler #1180
		- FileHandler #1180
		- MemcachedHandler #1180
		- RedisHandler #1180
	- Test/
		- FeatureTestCase #1427
	- Validation /
		- Rules #1345
		- Validation #1345
	- View/
		- Parser #1417
		- View #1357, #1377, #1410
	- Controller #1423
	- Entity #1369, #1373
	- Model #1345, #1380, #1373

- tests /
	- _support/_bootstrap.php #1397, #1443
	- Cache/Handlers/
		- MemcachedHandlerTest #1180, #1383
		- RedisHandlerTest #1180
	- HTTP/
		- Files/FileMovingTest #1424
		- DownloadResponseTest #1375
		- RedirectResponseTest #1387
		- ResponseTest #1375
	- Log/
		- FileHandlerTest #1425
	- Pager/
		- PagerRendererTest #1213
	- Session/
		- SessionTest  #1180
	- Test/
		- BootstrapFCPATHTest #1397
		- TestCaseTest #1390
	- Throttle/
		- ThrottleTest #1398
	- View/
		- ParserTest #1335
	- CommonFunctionsTest #1180

- user_guide_src /source/
	- database/
		- queries #1407
	- dbmgmt/
		- migration #1374, #1385, #1431
	- installation/
		- index	#1388
	- libraries/
		- pagination #1213
	- tutorial/
		- create_news_item #1442
	- changelog #1385

- /
	- composer.json #1388, #1418
	- .travis.yml #1394

PRs merged:
-----------

- #1443 Fixes unit test output not captured
- #1442 remove form view in application/View/ and form helper usage in create new items tutorial
- #1432 Use mb_strlen to get length of columns
- #1431 can't call run() method with params from commands migrations
- #1427 Fixes "options" request call parameter in FeatureTestCase
- #1416 performance improvement in Database\BaseResult
- #1425 Ensure FileHandlerTest uses MockFileHandler
- #1424 Fix FileMovingTest leaving cruft
- #1423 Fix Controller use validate bug
- #1422 fix Migrations.classNotFound
- #1418 normalize composer.json
- #1417 fix Parser::parsePairs always escapes
- #1416 remove $psr4['Tests\Support'] definition in application\Config\Autoload
- #1415 remove unneded "defined('BASEPATH') ...
- #1413 set more_entropy = true in all uniqid() usage
- #1412 function_exists() typo fixes on array_helper
- #1411 add missing break; in loop in View::render()
- #1410 Fix spark serve not working from commit 2d0b325
- #1407 Database: add missing call initialize() check on BaseConnection->prepare()
- #1406 Add missing parameter to RouteCollectionInterface
- #1405 Fix language string used in DownloadException
- #1402 Correct class namespacing in the user guide
- #1399 optional type hinting in guessExtension
- #1398 Tweak throttle testing
- #1397 Correcting FCPATH setting in tests/_support/_bootstrap.php
- #1396 only register PSR4 "Tests\Support" namespace in "testing" environment
- #1395 short array syntax in docs
- #1394 add php 7.3 to travis config
- #1390 Fixed not to output "Hello" at test execution
- #1389 Capitalize email filename
- #1388 Phpcs Auto-fix on commit
- #1387 Redirect to named route
- #1385 Fix migration page; udpate changelog
- #1384 add missing ResponseInterface contants
- #1383 fix TypeError in MemcachedHandler::__construct()
- #1381 Remove unused use statements
- #1380 count() improvement, use truthy check
- #1378 Update Filters language file
- #1377 fix monolog will cause an error
- #1376 Fix cannot use class Honeypot because already in use in App\Filters\Honeypot
- #1375 Give download a header conforming to RFC 6266
- #1374 Missing feature migration.
- #1373 Turning off casting for db insert/save 
- #1371 update method name in coding style
- #1370 Toolbar needs logging. Fixes #1258
- #1369 Remove invisible character
- #1368 UploadedFile->guessExtenstion()...
- #1360 rm --cached php_errors.log file
- #1357 Update template file is not .php compatibility
- #1345 is_unique tried to connect to default database instead of defined in DBGroup
- #1344 Not to quote unecessary table options
- #1343 Avoid add two single quote to constraint
- #1335 Review and improvements in databases drivers MySQLi, Postgre and SQLite
- #1213 URI segment as page number in Pagination
- #1180 using HTTP\Request instance to pull ip address

Version 4.0.0-alpha.2
=================================

Release Date: Oct 26, 2018

**Second alpha release of CodeIgniter4**

The list of changed files follows, with PR numbers shown.

application /
    - composer.json #1312
    - Config/Boot/development, production, testing #1312
    - Config/Paths #1341
    - Config/Routes #1281
    - Filters/Honeypot #1314
    - Views/errors/cli/error_404 #1272
    - Views/welcome_message #1342

public /
    - .htaccess #1281
    - index #1295, #1313

system /
    - CLI/
        - CommandRunner #1350, #1356
    - Commands/
        - Server/Serve #1313 
    - Config/
        - AutoloadConfig #1271
        - Services #1341
    - Database/
        - BaseBuilder #1217
        - BaseUtils #1209, #1329
        - Database #1339
        - MySQLi/Utils #1209
    - Debug/Toolbar/
        - Views/toolbar.css #1342
    - Exceptions/
        - CastException #1283
        - DownloadException #1239
        - FrameworkException #1313
    - Filters/
        - Filters #1239
    - Helpers/
        - cookie_helper #1286
        - form_helper #1244, #1327
        - url_helper #1321
        - xml_helper #1209
    - Honeypot/
        - Honeypot #1314
    - HTTP/
        - CliRequest #1303
        - CURLRequest #1303
        - DownloadResponse #1239
        - Exceptions/HTTPException #1303
        - IncomingRequest #1304, #1313
        - Negotiate #1306
        - RedirectResponse #1300, #1306, #1329
        - Response #1239, #1286
        - ResponseInterface #1239
        - URI #1300
    - Language/en/
        - Cast #1283
        - HTTP #1239
    - Router/
        - RouteCollection #1285, #1355
    - Test/
        - CIUnitTestCase #1312, #1361
        - FeatureTestCase #1282
    - CodeIgniter #1239 #1337
    - Common #1291
    - Entity #1283, #1311
    - Model #1311

tests /
    - API/
        - ResponseTraitTest #1302
    - Commands/
        - CommandsTest #1356
    - Database/
        - BaseBuilderTest #1217
        - Live/ModelTest #1311
    - Debug/
        - TimerTest #1273
    - Helpers/
        - CookieHelperTest #1286
    - Honeypot/
        - HoneypotTest #1314
    - HTTP/
        - Files/
            - FileMovingTest #1302
            - UploadedFileTest #1302
        - CLIRequestTest #1303
        - CURLRequestTest #1303
        - DownloadResponseTest #1239
        - NegotiateTest #1306
        - RedirectResponseTest #1300, #1306, #1329
        - ResponseTest #1239
    - I18n/
        - TimeTest #1273, #1316
    - Router/
        - RouteTest #1285, #1355
    - Test/
        - TestCaseEmissionsTest #1312
        - TestCaseTest #1312
    - View/
        - ParserTest #1311
    - EntityTest #1319


user_guide_src /source/
    - cli/
        - cli_request #1303
    - database/
        - query_builder #1217
        - utilities #1209
    - extending/
        - contributing #1280
    - general/
        - common_functions #1300, #1329
        - helpers #1291
        - managing_apps #1341
    - helpers/
        - xml_helper #1321
    - incoming/
        - controllers #1323
        - routing #1337
    - intro/
        - requirements #1280, #1303
    - installation/ #1280, #1303
        - troubleshooting #1265
    - libraries/
        - curlrequest #1303
        - honeypot #1314
        - sessions #1333
        - uploaded_files #1302
    - models/
        - entities #1283
    - outgoing/
        - response #1340
    - testing/
        - overview #1312
    - tutorial... #1265, #1281, #1294

/
    - spark #1305

PRs merged:
-----------

- #1361 Add timing assertion to CIUnitTestCase
- #1312 Add headerEmitted assertions to CIUnitTestCase
- #1356 Testing/commands
- #1355 Handle duplicate HTTP verb and generic rules properly
- #1350 Checks if class is instantiable and is a command
- #1348 Fix sphinx formatting in sessions
- #1347 Fix sphinx formatting in sessions
- #1342 Toolbar Styles
- #1341 Make viewpath configurable in Paths.php. Fixes #1296
- #1340 Update docs for downloads to reflect the need to return it. Fixes #1331
- #1339 Fix error where Forge class might not be returned. Fixes #1225
- #1337 Filter in the router Fixes #1315
- #1336 Revert alpha.2
- #1334 Proposed changelog for alpha.2
- #1333 Error in user guide for session config. Fixes #1330
- #1329 Tweaks
- #1327 FIX form_hidden and form_open - value escaping as is in form_input.
- #1323 Fix doc error : show_404() doesn't exist any more
- #1321 Added missing xml_helper UG page
- #1319 Testing/entity
- #1316 Refactor TimeTest
- #1314 Fix & expand Honeypot & its tests
- #1313 Clean exception
- #1311 Entities store an original stack of values to compare against so we d…
- #1306 Testing3/http
- #1305 Change chdir('public') to chdir($public)
- #1304 Refactor script name stripping in parseRequestURI()
- #1303 Testing/http
- #1302 Exception：No Formatter defined for mime type ''
- #1300 Allow redirect with Query Vars from the current request.
- #1295 Fix grammar in front controller comment.
- #1294 Updated final tutorial page. Fixes #1292
- #1291 Allows extending of helpers. Fixes #1264
- #1286 Cookies
- #1285 Ensure current HTTP verb routes are matched prior to any * matched ro…
- #1283 Entities
- #1282 system/Test/FeatureTestCase::setupRequest(), minor fixes phpdoc block…
- #1281 Tut
- #1280 Add contributing reference to user guide
- #1273 Fix/timing
- #1272 Fix undefined variable "heading" in cli 404
- #1271 remove inexistent "CodeIgniter\Loader" from AutoloadConfig::classmap
- #1269 Release notes & process
- #1266 Adjusting the release build scripts
- #1265 WIP Fix docs re PHP server
- #1245 Fix #1244 (form_hidden declaration)
- #1239 【Unsolicited PR】I changed the download method to testable.
- #1217 Optional parameter for resetSelect() call in Builder's countAll();
- #1209 Fix undefined function xml_convert at Database\BaseUtils


Version 4.0.0-alpha.1
=================================

Release Date: September 28, 2018

**Rewrite of the CodeIgniter framework**

Non-code changes:
    - User Guide adapted or rewritten
    - [System message translations repository](https://github.com/codeigniter4/CodeIgniter4-translations)
    - [Roadmap subforum](https://forum.codeigniter.com/forum-33.html) for more transparent planning

New core classes:
    - CodeIgniter (bootstrap)
    - Common (shared functions)
    - ComposerScripts (integrate third party tools)
    - Controller (base controller)
    - Model (base model)
    - Entity (entity encapsulation)

New packages:
    - API 
        - \\ ResponseTrait
    - Autoloader 
        - \\ AutoLoader, FileLocator
    - CLI 
        - \\ BaseCommand, CLI, CommandRunner, Console
    - Cache 
        - \\ CacheFactory, CacheInterface
        - \\ Handlers ... Dummy, File, Memcached, Predis, Redis, Wincache
    - Commands 
        - \\ Help, ListCommands
        - \\ Database \\ CreateMigration, MigrateCurrent, MigrateLatest, MigrateRefresh,
          MigrateRollback, MigrateStatus, MigrateVersion, Seed
        - \\ Server \\ Serve
        - \\ Sessions \\ CreateMigration
        - \\ Utilities \\ Namespaces, Routes
    - Config 
        -   \\ AutoloadConfig, BaseConfig, BaseService, Config, DotEnv, ForeignCharacters, 
            Routes, Services, View
    - Database
        -   \\ BaseBuilder, BaseConnection, BasePreparedQuery, BaseResult, BaseUtils, Config,
            ConnectionInterface, Database, Forge, Migration, MigrationRunner, PreparedQueryInterface, Query,
            QueryInterface, ResultInterface, Seeder
        -   \\ MySQLi \\ Builder, Connection, Forge, PreparedQuery, Result
        -   \\ Postgre \\ Builder, Connection, Forge, PreparedQuery, Result, Utils
        -   \\ SQLite3 \\ Builder, Connection, Forge, PreparedQuery, Result, Utils
    - Debug
        - \\ Exceptions, Iterator, Timer, Toolbar
        - \\ Toolbar \\ Collectors...
    - Email
        - \\ Email
    - Events
        - \\ Events
    - Files
        - \\ File
    - Filters
        - \\ FilterInterface, Filters  
    - Format
        - \\ FormatterInterface, JSONFormatter, XMLFormatter
    - HTTP
        -   \\ CLIRequest, CURLRequest, ContentSecurityPolicy, Header,
            IncomingRequest, Message, Negotiate, Request, RequestInterface,
            Response, ResponseInterface, URI, UserAgent
        -   \\ Files \\ FileCollection, UploadedFile, UploadedFileInterface
    - Helpers 
        -   ... array, cookie, date, filesystem, form, html, inflector, number,
            security, text, url
    - Honeypot 
        - \\ Honeypot
    - I18n
        - \\ Time, TimeDifference
    - Images
        - \\ Image, ImageHandlerInterface
        - \\ Handlers ... Base, GD, ImageMagick
    - Language
        - \\ Language
    - Log
        -   Logger, LoggerAwareTrait
        -   \\ Handlers ...  Base, ChromeLogger, File, HandlerInterface
    - Pager
        - \\ Pager, PagerInterface, PagerRenderer
    - Router 
        - \\ RouteCollection, RouteCollectionInterface, Router, RouterInterface
    - Security 
        - \\ Security
    - Session
        -   \\ Session, SessionInterface
        -   \\ Handlers ... Base, File, Memcached, Redis
    - Test 
        - \\ CIDatabaseTestCase, CIUnitTestCase, FeatureResponse, FeatureTestCase, ReflectionHelper
        - \\ Filters \\ CITestStreamFilter
    - ThirdParty (bundled)
        - \\ Kint (for \\Debug)
        - \\ PSR \\ Log (for \\Log)
        - \\ ZendEscaper \\ Escaper (for \\View)
    - Throttle
        - \\ Throttler, ThrottlerInterface
    - Typography
        - \\ Typography
    - Validation
        - \\ CreditCardRules, FileRules, FormatRules, Rules, Validation, ValidationInterface
    - View
        -   \\ Cell, Filters, Parser, Plugins, RendererInterface, View