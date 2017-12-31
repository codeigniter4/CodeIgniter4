##########
Change Log
##########

Version 4.0-Pre-Alpha1
======================

**Rewrite of the CodeIgniter framework**

Release Date: Not Released

New core classes:

    - CodeIgniter (bootstrap)
    - Common (shared functions)
    - ComposerScripts (integrate third party tools)
    - Controller (base controller)
    - Model (base model)

New packages:

    - Autoloader \\ AutoLoader, FileLocator
    - CLI \\ CLI
    - Commands \\ MigrationsCommand
    - Config \\ AutoloadConfig, BaseConfig, DotEnv, Routes
    - Database

        -   \\ BaseBuilder, BaseConnection, BaseResult, BaseUtils, Config,
            ConnectionInterface, Database, Forge, Migration, MigrationRunner, Query,
            QueryInterface, ResultInterface, Seeder
        -   \\ MySQLi \\ Builder, Connection, Forge, Result
        -   \\ Postgre \\ Builder, Connection, Forge, Result, Utils

    - Debug

        - \\ CustomExceptions, Exceptions, Iterator, Timer, Toolbar
        - Kint \\ Kint **third party**

    - HTTP

        -   \\ CLIRequest, CURLRequest, ContentSecurityPolicy, Header,
            IncomingRequest, Message, Negotiate, Request, RequestInterface,
            Response, ResponseInterface, URI
        -   \\ Files \\ FileCollection, UploadedFile, UploadedFileInterface

    - Helpers ... uri
    - Events \\ Events
    - Log

        -   Logger, LoggerAwareTrait
        -   \\ Handlers \\  BaseHandler, ChromeLoggerHandler, FileHandler, HandlerInterface
        -   Psr \\ Log **third party**

    - Router \\ RouteCollection, RouteCollectionInterface, Router, RouterInterface
    - Security \\ Security
    - Session

        -   \\ Session, SessionInterface
        -   \\ Handlers \\ BaseHandler, FileHandler, MemcachedHandler, RedisHandler

    - Test \\ CIDatabaseTestCase, CIUnitTestCase, ReflectionHelper
    - View

        -   Zend \\ Escaper, Exception \\ ... **third party**
        -   RendererInterface, View

User Guide adapted or rewritten.
