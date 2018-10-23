##########
Change Log
##########

Version 4.0.0-alpha.2
=================================

Release Date: October 22, 2018

**Second alpha release of CodeIgniter4**

The list of changed files follows, with PR numbers shown.
If you open this page on the repo github site, they will link
to the PRs in question.

application /
    - Config/Routes #1281
    - Filters/Honeypot #1314
    - Views/errors/cli/error_404 #1272

public /
    - .htaccess #1281
    - index #1295, #1313

system /
    - Commands/
        - Server/Serve #1313 
    - Config/
        - AutoloadConfig #1271
    - Database/
        - BaseBuilder #1217
        - BaseUtils #1209, #1329
        - MySQLi/Utils #1209
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
        - RouteCollection #1285
    - Test/
        - FeatureTestCase #1282
    - CodeIgniter #1239
    - Common #1291
    - Entity #1283, #1311
    - Model #1311

tests /
    - API/
        - ResponseTraitTest #1302
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
        - RouteTest #1285
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
    - helpers/
        - xml_helper #1321
    - incoming/
        - controllers #1323
    - intro/
        - requirements #1280, #1303
    - installation/ #1280, #1303
        - troubleshooting #1265
    - libraries/
        - curlrequest #1303
        - honeypot #1314
        - uploaded_files #1302
    - models/
        - entities #1283
    - tutorial... #1265, #1281, #1294

/
    - spark #1305


Version 4.0.0-alpha.1
=================================

Release Date: September 28, 2018

**Rewrite of the CodeIgniter framework**

Non-code changes:
    - User Guide adapted or rewritten
    - [System message translations repository](https://github.com/bcit-ci/CodeIgniter4-translations)
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
